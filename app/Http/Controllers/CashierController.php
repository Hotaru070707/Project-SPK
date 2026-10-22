<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CashierController extends Controller
{
    public function index()
    {
        $todayOrders = Order::query()
            ->where('type', 'offline')
            ->where('cashier_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->get();

        $totalOmsetToday = $todayOrders->where('payment_status', 'paid')->sum('total');
        $totalTrxToday = $todayOrders->count();
        $recentOrders = Order::query()
            ->where(function ($query): void {
                $query->where('type', 'online')
                    ->orWhere(function ($query): void {
                        $query->where('type', 'offline')
                            ->where('cashier_id', Auth::id());
                    });
            })
            ->with('customer')
            ->latest()
            ->take(6)
            ->get();

        return view('cashier.index', compact('totalOmsetToday', 'totalTrxToday', 'recentOrders'));
    }

    public function pos()
    {
        $products = Product::where('is_active', true)->with('category')->get();
        $customers = User::query()
            ->where('role', 'customer')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        $transactionKey = (string) Str::uuid();
        $cashierId = Auth::id();

        return view('cashier.pos', compact('products', 'customers', 'transactionKey', 'cashierId'));
    }

    public function storeTransaction(Request $request)
    {
        $idempotencyKey = $request->validate([
            'idempotency_key' => ['required', 'uuid'],
        ])['idempotency_key'];

        $existingOrder = Order::query()
            ->with(['customer', 'items'])
            ->where('idempotency_key', $idempotencyKey)
            ->where('cashier_id', Auth::id())
            ->first();
        if ($existingOrder) {
            return response()->json([
                'success' => true,
                ...$this->receiptPayload($existingOrder),
                'next_idempotency_key' => (string) Str::uuid(),
                'message' => 'Transaksi ini sudah berhasil diproses.',
            ]);
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'in:cash'],
            'cash_given' => ['required', 'numeric', 'min:0'],
            'customer_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', 'customer')->where('is_active', true),
            ],
        ]);

        $items = collect($validated['items'])
            ->map(fn (array $item): array => [
                'id' => (int) $item['id'],
                'qty' => (int) $item['qty'],
            ])
            ->sortBy('id')
            ->values();

        try {
            $receipt = DB::transaction(function () use ($validated, $items, $idempotencyKey): array {
                $products = Product::query()
                    ->whereIn('id', $items->pluck('id'))
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
                if ($products->count() !== $items->count()) {
                    throw ValidationException::withMessages([
                        'items' => ['Satu atau beberapa produk sudah tidak tersedia. Muat ulang daftar produk.'],
                    ]);
                }

                $stockIssues = $items
                    ->filter(fn (array $item): bool => $products[$item['id']]->stock < $item['qty'])
                    ->map(fn (array $item): string => $products[$item['id']]->name)
                    ->unique()
                    ->values();
                if ($stockIssues->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => ['Stok tidak mencukupi untuk: '.$stockIssues->implode(', ').'.'],
                    ]);
                }

                $customer = isset($validated['customer_id'])
                    ? User::query()->whereKey($validated['customer_id'])->where('role', 'customer')->firstOrFail()
                    : null;
                $subtotal = $items->sum(fn (array $item): float => (float) $products[$item['id']]->sell_price * $item['qty']);
                $discount = 0;
                $total = round($subtotal, 2);
                $cashGiven = (float) $validated['cash_given'];
                if ($cashGiven < $total) {
                    throw ValidationException::withMessages([
                        'cash_given' => ['Nominal uang tunai harus minimal sama dengan total transaksi.'],
                    ]);
                }

                $order = Order::create([
                    'order_number' => 'POS-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                    'type' => 'offline',
                    'cashier_id' => Auth::id(),
                    'customer_id' => $customer?->id,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                    'cash_given' => $cashGiven,
                    'change_amount' => $cashGiven - $total,
                    'payment_method' => 'cash',
                    'payment_status' => 'paid',
                    'order_status' => 'completed',
                    'idempotency_key' => $idempotencyKey,
                    'paid_at' => now(),
                ]);

                foreach ($items as $item) {
                    $product = $products[$item['id']];
                    $price = (float) $product->sell_price;
                    $stockBefore = $product->stock;
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $price,
                        'cost_price' => $product->cost_price,
                        'qty' => $item['qty'],
                        'subtotal' => $price * $item['qty'],
                    ]);

                    $product->decrement('stock', $item['qty']);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => Auth::id(),
                        'order_id' => $order->id,
                        'type' => 'out',
                        'qty' => $item['qty'],
                        'stock_before' => $stockBefore,
                        'stock_after' => $product->fresh()->stock,
                        'reason' => 'offline_sale',
                    ]);
                }

                return ['order_id' => $order->id];
            });
        } catch (QueryException $exception) {
            $exceptionMessage = strtolower($exception->getMessage());
            $isIdempotencyConflict = str_contains($exceptionMessage, 'idempotency_key')
                && (str_contains($exceptionMessage, 'unique') || str_contains($exceptionMessage, 'duplicate'));
            if (! $isIdempotencyConflict) {
                throw $exception;
            }

            $existingOrder = Order::query()
                ->with(['customer', 'items'])
                ->where('idempotency_key', $idempotencyKey)
                ->where('cashier_id', Auth::id())
                ->first();

            if (! $existingOrder) {
                throw $exception;
            }

            return response()->json([
                'success' => true,
                ...$this->receiptPayload($existingOrder),
                'next_idempotency_key' => (string) Str::uuid(),
                'message' => 'Transaksi ini sudah berhasil diproses.',
            ]);
        }

        $order = Order::query()->with(['customer', 'items'])->findOrFail($receipt['order_id']);

        return response()->json([
            'success' => true,
            ...$this->receiptPayload($order),
            'next_idempotency_key' => (string) Str::uuid(),
            'message' => 'Transaksi POS berhasil disimpan dan struk siap dicetak.',
        ]);
    }

    public function transactions()
    {
        $query = Order::query()
            ->where(function ($query): void {
                $query->where('type', 'online')
                    ->orWhere(function ($query): void {
                        $query->where('type', 'offline');
                        if (Auth::user()->isCashier()) {
                            $query->where('cashier_id', Auth::id());
                        }
                    });
            });
        $orders = $query->with(['items', 'customer'])->latest()->paginate(15);

        return view('cashier.transactions', compact('orders'));
    }

    public function showOrder(Order $order): View
    {
        abort_unless($order->type === 'online', 404);

        $order->load(['items.product', 'customer', 'address', 'cashier']);

        $paymentStatusLabels = [
            'unpaid' => 'Belum Dibayar',
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Lunas',
            'expired' => 'Kedaluwarsa',
            'refunded' => 'Dikembalikan',
        ];
        $orderStatusLabels = [
            'pending_payment' => 'Menunggu Pembayaran',
            'processing' => 'Sedang Diproses',
            'packed' => 'Siap Diambil',
            'shipped' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'void' => 'Void',
        ];

        return view('cashier.orders.show', compact('order', 'paymentStatusLabels', 'orderStatusLabels'));
    }

    public function markOrderReadyForPickup(Order $order): RedirectResponse
    {
        $error = $this->transitionOnlineOrder($order, 'processing', 'packed');

        if ($error !== null) {
            return redirect()->route('cashier.orders.show', $order->order_number)->with('error', $error);
        }

        return redirect()->route('cashier.orders.show', $order->order_number)
            ->with('success', 'Pesanan berhasil ditandai siap diambil.');
    }

    public function completeOnlineOrder(Order $order): RedirectResponse
    {
        $error = $this->transitionOnlineOrder($order, 'packed', 'completed', (int) Auth::id());

        if ($error !== null) {
            return redirect()->route('cashier.orders.show', $order->order_number)->with('error', $error);
        }

        return redirect()->route('cashier.orders.show', $order->order_number)
            ->with('success', 'Penyerahan berhasil dikonfirmasi dan pesanan telah selesai.');
    }

    private function transitionOnlineOrder(
        Order $order,
        string $expectedStatus,
        string $nextStatus,
        ?int $cashierId = null
    ): ?string {
        return DB::transaction(function () use ($order, $expectedStatus, $nextStatus, $cashierId): ?string {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->type !== 'online') {
                return 'Aksi ini hanya tersedia untuk pesanan online.';
            }

            if ($lockedOrder->payment_status !== 'paid') {
                return 'Pembayaran belum dikonfirmasi. Pesanan tidak dapat diproses.';
            }

            if ($lockedOrder->order_status !== $expectedStatus) {
                return 'Status pesanan sudah berubah atau tidak sesuai untuk tindakan ini.';
            }

            $attributes = ['order_status' => $nextStatus];

            if ($cashierId !== null) {
                $attributes['cashier_id'] = $cashierId;
            }

            $lockedOrder->update($attributes);

            return null;
        });
    }

    private function receiptPayload(Order $order): array
    {
        return [
            'order_number' => $order->order_number,
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) $order->discount,
            'total' => (float) $order->total,
            'cash_given' => (float) $order->cash_given,
            'change' => (float) $order->change_amount,
            'customer_name' => $order->customer?->name ?? 'Pelanggan Umum',
            'items' => $order->items->map(fn (OrderItem $item): array => [
                'name' => $item->product_name,
                'qty' => $item->qty,
                'price' => (float) $item->price,
                'subtotal' => (float) $item->subtotal,
            ])->all(),
        ];
    }
}

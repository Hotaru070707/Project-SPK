<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\MidtransSnapService;
use App\Services\StockService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }

        return view('customer.cart.index', compact('cart', 'subtotal'));
    }

    public function add(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'redirect' => route('login'),
                    'message' => 'Silakan masuk sebagai pelanggan terlebih dahulu untuk menambahkan produk ke keranjang.',
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Silakan masuk sebagai pelanggan terlebih dahulu untuk menambahkan produk ke keranjang.');
        }

        abort_unless($user->isCustomer(), 403, 'Hanya pelanggan yang dapat menambahkan produk ke keranjang.');

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);
        $qty = (int) $request->qty;

        if (isset($cart[$product->id])) {
            $cart[$product->id]['qty'] += $qty;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->sell_price,
                'cost_price' => (float) $product->cost_price,
                'unit' => $product->unit,
                'category' => $product->category->name ?? 'Sembako',
                'image' => $product->image_url,
                'qty' => $qty,
            ];
        }

        session()->put('cart', $cart);
        $cartCount = array_sum(array_column($cart, 'qty'));
        session()->put('cart_count', $cartCount);

        $message = "{$product->name} berhasil ditambahkan ke keranjang!";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => $cartCount,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                ],
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'qty' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);
        if (isset($cart[$request->product_id])) {
            $cart[$request->product_id]['qty'] = (int) $request->qty;
            session()->put('cart', $cart);
            session()->put('cart_count', array_sum(array_column($cart, 'qty')));
        }

        return redirect()->route('cart.index')->with('success', 'Jumlah belanja berhasil diperbarui.');
    }

    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
        ]);

        $cart = session()->get('cart', []);
        if (isset($cart[$request->product_id])) {
            unset($cart[$request->product_id]);
            session()->put('cart', $cart);
            session()->put('cart_count', array_sum(array_column($cart, 'qty')));
        }

        return redirect()->route('cart.index')->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    public function checkout()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja Anda masih kosong.');
        }

        $idempotencyKey = session()->get('checkout_idempotency_key');
        if (! is_string($idempotencyKey)) {
            $idempotencyKey = (string) Str::uuid();
            session()->put('checkout_idempotency_key', $idempotencyKey);
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }

        return view('customer.cart.checkout', compact('cart', 'subtotal', 'idempotencyKey'));
    }

    public function processCheckout(Request $request, MidtransSnapService $midtrans, StockService $stockService)
    {
        $validated = $request->validate([
            'idempotency_key' => ['nullable', 'uuid'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:midtrans_qris,cash'],
        ]);
        $validated['idempotency_key'] = $validated['idempotency_key']
            ?? session()->get('checkout_idempotency_key')
            ?? (string) Str::uuid();
        session()->put('checkout_idempotency_key', $validated['idempotency_key']);

        $existingOrder = Order::query()
            ->where('idempotency_key', $validated['idempotency_key'])
            ->where('customer_id', Auth::id())
            ->first();

        if ($existingOrder !== null) {
            return $this->checkoutResponse($existingOrder);
        }

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong.');
        }

        $items = collect($cart)
            ->map(fn (array $item, string|int $key): array => [
                'product_id' => (int) ($item['id'] ?? $key),
                'qty' => filter_var($item['qty'] ?? null, FILTER_VALIDATE_INT),
            ])
            ->sortBy('product_id')
            ->values();

        if ($items->contains(fn (array $item): bool => $item['product_id'] < 1 || $item['qty'] === false || $item['qty'] < 1)) {
            throw ValidationException::withMessages([
                'cart' => ['Keranjang berisi jumlah atau produk yang tidak valid. Muat ulang keranjang dan coba kembali.'],
            ]);
        }

        try {
            $order = DB::transaction(function () use ($validated, $items, $midtrans, $stockService): Order {
                if (Auth::check()) {
                    User::query()->lockForUpdate()->findOrFail(Auth::id());
                }

                $existingOrder = Order::query()
                    ->where('idempotency_key', $validated['idempotency_key'])
                    ->where('customer_id', Auth::id())
                    ->first();
                if ($existingOrder !== null) {
                    return $existingOrder;
                }

                $products = Product::query()
                    ->whereIn('id', $items->pluck('product_id'))
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($products->count() !== $items->count()) {
                    throw ValidationException::withMessages([
                        'cart' => ['Satu atau beberapa produk sudah tidak tersedia. Muat ulang keranjang.'],
                    ]);
                }

                $stockIssues = $items
                    ->filter(fn (array $item): bool => $products[$item['product_id']]->stock < $item['qty'])
                    ->map(fn (array $item): string => $products[$item['product_id']]->name)
                    ->unique()
                    ->values();
                if ($stockIssues->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'cart' => ['Stok tidak mencukupi untuk: '.$stockIssues->implode(', ').'.'],
                    ]);
                }

                $subtotal = $items->sum(
                    fn (array $item): float => (float) $products[$item['product_id']]->sell_price * $item['qty']
                );
                $total = (int) round($subtotal);
                $orderNumber = 'ONL-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
                $orderNotes = "Pemesan: {$validated['recipient_name']} ({$validated['phone']})";
                if (! empty($validated['notes'])) {
                    $orderNotes .= " | Catatan: {$validated['notes']}";
                }

                $order = Order::query()->create([
                    'order_number' => $orderNumber,
                    'idempotency_key' => $validated['idempotency_key'],
                    'type' => 'online',
                    'customer_id' => Auth::id(),
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'total' => $total,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $validated['payment_method'] === 'cash' ? 'pending' : 'unpaid',
                    'order_status' => 'pending_payment',
                    'midtrans_order_id' => $validated['payment_method'] === 'midtrans_qris' ? $orderNumber : null,
                    'notes' => $orderNotes,
                ]);

                foreach ($items as $item) {
                    $product = $products[$item['product_id']];
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $product->sell_price,
                        'cost_price' => $product->cost_price,
                        'qty' => $item['qty'],
                        'subtotal' => (float) $product->sell_price * $item['qty'],
                    ]);

                    $stockService->setStock(
                        $product,
                        $product->stock - $item['qty'],
                        'out',
                        Auth::id(),
                        'online_order:'.$order->order_number,
                        $order->id
                    );
                }

                if ($validated['payment_method'] === 'midtrans_qris') {
                    $payment = $midtrans->createTransaction($order, $validated['recipient_name'], $validated['phone']);
                    $order->update([
                        'midtrans_token' => $payment['token'],
                        'midtrans_payload' => ['redirect_url' => $payment['redirect_url']],
                    ]);
                }

                return $order;
            });
        } catch (ConnectionException|RuntimeException $exception) {
            report($exception);

            return back()->with('error', 'Pesanan gagal dibuat. Periksa konfigurasi Midtrans atau coba kembali.');
        }

        session()->forget(['cart', 'cart_count']);
        session()->put('checkout_idempotency_key', (string) Str::uuid());

        return $this->checkoutResponse($order, true);
    }

    private function checkoutResponse(Order $order, bool $isNewOrder = false): RedirectResponse
    {
        $redirectUrl = data_get($order->midtrans_payload, 'redirect_url');
        if (
            $order->payment_method === 'midtrans_qris'
            && is_string($redirectUrl)
            && $order->payment_status !== 'expired'
            && $order->order_status !== 'cancelled'
        ) {
            return redirect()->away($redirectUrl);
        }

        return redirect()->route('orders.show', $order->order_number)
            ->with($isNewOrder ? 'success' : 'info', "Pesanan #{$order->order_number} berhasil dibuat.");
    }
}

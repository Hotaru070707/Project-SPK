<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\TopsisResult;
use App\Models\User;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function index(): View
    {
        $totalProducts = Product::where('is_active', true)->count();
        $totalStockUnits = Product::where('is_active', true)->sum('stock');
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->get();
        $todayOrders = Order::where('payment_status', 'paid')
            ->whereDate('paid_at', today());
        $totalRevenue = (clone $todayOrders)->sum('total');
        $totalOrders = (clone $todayOrders)->count();
        $salesStart = now()->subDays(6)->startOfDay();
        $revenueByDay = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [$salesStart, now()->endOfDay()])
            ->selectRaw('DATE(paid_at) as report_date, SUM(total) as revenue')
            ->groupBy('report_date')
            ->pluck('revenue', 'report_date');
        $salesChart = collect(range(6, 0))->map(function (int $daysAgo) use ($revenueByDay): array {
            $date = now()->subDays($daysAgo);
            $dateKey = $date->toDateString();

            return [
                'label' => $date->translatedFormat('D'),
                'date' => $dateKey,
                'revenue' => (float) ($revenueByDay[$dateKey] ?? 0),
            ];
        });

        $recentOrders = Order::with(['customer', 'items'])
            ->latest()
            ->take(6)
            ->get();

        $latestTopsis = TopsisResult::latest()->first();

        return view('owner.dashboard', compact(
            'totalProducts',
            'totalStockUnits',
            'lowStockProducts',
            'totalOrders',
            'totalRevenue',
            'salesChart',
            'recentOrders',
            'latestTopsis'
        ));
    }

    public function products(): View
    {
        $products = Product::with('category')->latest()->paginate(15);
        $categories = Category::all();

        return view('owner.products.index', compact('products', 'categories'));
    }

    public function storeProduct(Request $request, StockService $stockService): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:64',
            'cost_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'supplier_lead_time_days' => 'required|integer|min:0|max:365',
            'unit' => 'required|string|max:20',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $validated['slug'] = Str::slug($validated['name']).'-'.rand(100, 999);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        $initialStock = (int) $validated['stock'];
        $validated['stock'] = 0;

        DB::transaction(function () use ($validated, $initialStock, $stockService): void {
            $product = Product::query()->create($validated);
            if ($initialStock > 0) {
                $stockService->setStock(
                    $product,
                    $initialStock,
                    'in',
                    auth()->id(),
                    'Stok awal saat produk dibuat'
                );
            }
        });

        return redirect()->route('admin.products')->with('success', 'Produk sembako berhasil ditambahkan!');
    }

    public function updateProduct(Request $request, int $id, StockService $stockService): RedirectResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:64',
            'cost_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'supplier_lead_time_days' => 'required|integer|min:0|max:365',
            'stock_change_reason' => 'nullable|string|max:255',
            'adjust_stock' => 'nullable|boolean',
            'unit' => 'required|string|max:20',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        } elseif ($request->boolean('remove_image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = null;
        }

        DB::transaction(function () use ($product, $validated, $request, $stockService): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $stockBefore = $lockedProduct->stock;
            $shouldAdjustStock = $request->boolean('adjust_stock')
                || filled($validated['stock_change_reason'] ?? null)
                || (
                    ! $request->exists('adjust_stock')
                    && (int) $validated['stock'] !== $stockBefore
                );
            $stockAfter = $shouldAdjustStock ? (int) $validated['stock'] : $stockBefore;
            $reason = $validated['stock_change_reason'] ?? null;
            if ($stockAfter !== $stockBefore && blank($reason)) {
                throw ValidationException::withMessages([
                    'stock_change_reason' => ['Alasan wajib diisi jika stok produk diubah.'],
                ]);
            }

            unset($validated['stock_change_reason']);
            unset($validated['adjust_stock']);
            unset($validated['stock']);
            $lockedProduct->update($validated);

            if ($stockAfter !== $stockBefore) {
                $stockService->setStock(
                    $lockedProduct,
                    $stockAfter,
                    'adjustment',
                    auth()->id(),
                    $reason
                );
            }
        });

        return redirect()->route('admin.products')->with('success', 'Data produk berhasil diperbarui!');
    }

    public function deleteProduct(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Produk berhasil dihapus.');
    }

    public function categories(): View
    {
        $categories = Category::withCount('products')->get();

        return view('owner.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        Category::create($validated);

        return redirect()->route('admin.categories')->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    public function orders(Request $request): View
    {
        $validated = $request->validate([
            'type' => ['nullable', Rule::in(['online', 'offline'])],
            'payment_status' => ['nullable', Rule::in(['unpaid', 'pending', 'paid', 'expired', 'refunded'])],
            'order_status' => ['nullable', Rule::in(['pending_payment', 'processing', 'packed', 'shipped', 'completed', 'cancelled', 'void'])],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $ordersQuery = Order::query()->with(['customer', 'cashier', 'items.product']);

        $orders = $ordersQuery
            ->when(
                $validated['type'] ?? null,
                fn (Builder $query, string $type): Builder => $query->where('type', $type)
            )
            ->when(
                $validated['payment_status'] ?? null,
                fn (Builder $query, string $paymentStatus): Builder => $query->where('payment_status', $paymentStatus)
            )
            ->when(
                $validated['order_status'] ?? null,
                function (Builder $query, string $status): Builder {
                    if ($status === 'cancelled') {
                        return $query->whereIn('order_status', ['cancelled', 'void']);
                    }

                    return $query->where('order_status', $status);
                }
            )
            ->when(
                $validated['start_date'] ?? null,
                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date)
            )
            ->when(
                $validated['end_date'] ?? null,
                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date)
            )
            ->when(
                $validated['search'] ?? null,
                function (Builder $query, string $search): Builder {
                    return $query->where(function (Builder $query) use ($search): void {
                        $query->where('order_number', 'like', '%'.$search.'%')
                            ->orWhere('notes', 'like', '%'.$search.'%')
                            ->orWhereHas('customer', fn (Builder $customerQuery): Builder => $customerQuery->where('name', 'like', '%'.$search.'%'));
                    });
                }
            )
            ->latest('created_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Order::query()->count(),
            'awaiting_payment' => Order::query()
                ->where('type', 'online')
                ->whereIn('payment_status', ['unpaid', 'pending'])
                ->where('order_status', 'pending_payment')
                ->count(),
            'processing' => Order::query()->where('order_status', 'processing')->count(),
            'completed' => Order::query()->where('order_status', 'completed')->count(),
        ];

        $orderStatusLabels = [
            'pending_payment' => 'Menunggu Pembayaran',
            'processing' => 'Diproses',
            'packed' => 'Siap Diambil',
            'shipped' => 'Dalam Pengiriman',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'void' => 'Void (dibatalkan)',
        ];
        $paymentMethodLabels = [
            'cash' => 'Tunai',
            'manual_transfer' => 'Transfer Manual',
            'midtrans_qris' => 'Midtrans QRIS',
            'midtrans_va' => 'Midtrans VA',
            'midtrans_ewallet' => 'Midtrans E-Wallet',
        ];

        return view('owner.orders.index', compact(
            'orders',
            'summary',
            'validated',
            'orderStatusLabels',
            'paymentMethodLabels'
        ));
    }

    public function showOrder(Order $order): View
    {
        $order->load([
            'customer',
            'cashier',
            'paymentReviewer',
            'items.product',
            'stockMovements.product',
            'stockMovements.user',
        ]);

        return view('owner.orders.show', compact('order'));
    }

    public function confirmManualTransfer(Order $order): RedirectResponse
    {
        $result = DB::transaction(function () use ($order): array {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if (
                $lockedOrder->type !== 'online'
                || $lockedOrder->payment_method !== 'manual_transfer'
                || $lockedOrder->order_status !== 'pending_payment'
                || ! in_array($lockedOrder->payment_status, ['unpaid', 'pending'], true)
            ) {
                return [
                    'error' => "Pesanan #{$lockedOrder->order_number} bukan pembayaran transfer manual yang masih menunggu konfirmasi.",
                ];
            }

            $lockedOrder->update([
                'payment_status' => 'paid',
                'order_status' => 'processing',
                'payment_verification_status' => 'approved',
                'payment_rejection_reason' => null,
                'payment_reviewed_by' => auth()->id(),
                'payment_reviewed_at' => now(),
                'paid_at' => now(),
            ]);

            return ['success' => "Pembayaran transfer manual pesanan #{$lockedOrder->order_number} dikonfirmasi lunas."];
        });

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->back()->with('success', $result['success']);
    }

    public function cancelManualTransfer(Order $order, StockService $stockService): RedirectResponse
    {
        $result = DB::transaction(function () use ($order, $stockService): array {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if (
                $lockedOrder->type !== 'online'
                || $lockedOrder->payment_method !== 'manual_transfer'
                || $lockedOrder->order_status !== 'pending_payment'
                || ! in_array($lockedOrder->payment_status, ['unpaid', 'pending'], true)
            ) {
                return ['error' => "Pesanan #{$lockedOrder->order_number} tidak dapat dibatalkan dari status saat ini."];
            }

            $stockService->restoreUnpaidOrderStock(
                $lockedOrder,
                auth()->id(),
                'online_order_cancelled:'.$lockedOrder->order_number
            );
            $lockedOrder->update([
                'order_status' => 'cancelled',
                'payment_verification_status' => 'not_required',
                'payment_rejection_reason' => null,
            ]);

            return ['success' => "Pesanan #{$lockedOrder->order_number} dibatalkan dan reservasi stok dilepas."];
        });

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->back()->with('success', $result['success']);
    }

    public function customers(): View
    {
        $customers = User::where('role', 'customer')->withCount('orders')->get();

        return view('owner.customers.index', compact('customers'));
    }

    public function stockMovements(): View
    {
        $products = Product::query()->where('is_active', true)->orderBy('name')->get();
        $movements = StockMovement::query()
            ->with(['product', 'user', 'order'])
            ->latest()
            ->paginate(20);

        return view('owner.stock.index', compact('products', 'movements'));
    }

    public function storeStockMovement(Request $request, StockService $stockService): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:in,out,adjustment'],
            'qty' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if ($validated['type'] !== 'adjustment' && $validated['qty'] < 1) {
            throw ValidationException::withMessages([
                'qty' => ['Jumlah stok masuk atau keluar minimal satu unit.'],
            ]);
        }

        DB::transaction(function () use ($validated, $stockService): void {
            $product = Product::query()->lockForUpdate()->findOrFail($validated['product_id']);
            $stockBefore = $product->stock;
            $stockAfter = match ($validated['type']) {
                'in' => $stockBefore + $validated['qty'],
                'out' => $stockBefore - $validated['qty'],
                'adjustment' => $validated['qty'],
            };

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'qty' => ["Stok {$product->name} tidak mencukupi untuk pengeluaran tersebut."],
                ]);
            }

            $stockService->setStock(
                $product,
                $stockAfter,
                $validated['type'],
                auth()->id(),
                $validated['reason']
            );
        });

        return redirect()->route('admin.stock')->with('success', 'Pergerakan stok berhasil dicatat.');
    }

    public function reports(Request $request): View
    {
        [$startDate, $endDate] = $this->reportPeriod($request);
        $ordersQuery = $this->paidOrdersBetween($startDate, $endDate);
        $orders = (clone $ordersQuery)->with(['customer', 'items'])->latest('paid_at')->paginate(20)->withQueryString();
        $revenue = (clone $ordersQuery)->sum('total');
        $grossProfit = OrderItem::query()
            ->whereHas('order', function ($query) use ($startDate, $endDate): void {
                $query->where('payment_status', 'paid')->whereBetween('paid_at', [$startDate, $endDate]);
            })
            ->sum(DB::raw('(price - cost_price) * qty'));
        $totalTransactions = (clone $ordersQuery)->count();
        $topProducts = OrderItem::query()
            ->selectRaw('product_name, SUM(qty) as units_sold, SUM(subtotal) as revenue, SUM((price - cost_price) * qty) as gross_profit')
            ->whereHas('order', function ($query) use ($startDate, $endDate): void {
                $query->where('payment_status', 'paid')->whereBetween('paid_at', [$startDate, $endDate]);
            })
            ->groupBy('product_name')
            ->orderByDesc('units_sold')
            ->limit(10)
            ->get();
        $lowStockProducts = Product::query()
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->get();
        $movements = StockMovement::query()
            ->with('product')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->limit(20)
            ->get();

        return view('owner.reports.index', compact(
            'startDate',
            'endDate',
            'orders',
            'totalTransactions',
            'revenue',
            'grossProfit',
            'topProducts',
            'lowStockProducts',
            'movements'
        ));
    }

    public function exportSalesReport(Request $request): StreamedResponse
    {
        [$startDate, $endDate] = $this->reportPeriod($request);
        $ordersQuery = $this->paidOrdersBetween($startDate, $endDate);

        return response()->streamDownload(function () use ($ordersQuery): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['No. Transaksi', 'Tanggal', 'Kanal', 'Pelanggan', 'Metode Pembayaran', 'Omzet', 'Laba Kotor'], ';', '"', '\\');

            (clone $ordersQuery)->with(['customer', 'items'])->orderBy('id')->chunk(200, function ($orders) use ($output): void {
                foreach ($orders as $order) {
                    $grossProfit = $order->items->sum(fn ($item) => ($item->price - $item->cost_price) * $item->qty);
                    fputcsv($output, [
                        $order->order_number,
                        $order->paid_at?->format('Y-m-d H:i:s'),
                        $order->type,
                        $order->customer?->name ?? 'Pelanggan Kasir',
                        $order->payment_method,
                        $order->total,
                        number_format($grossProfit, 2, '.', ''),
                    ], ';', '"', '\\');
                }
            });

            fclose($output);
        }, 'laporan-penjualan.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function users(): View
    {
        $cashiers = User::query()
            ->where('role', 'cashier')
            ->orderBy('name')
            ->paginate(20);

        return view('owner.users.index', compact('cashiers'));
    }

    public function storeCashier(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        $validated['role'] = 'cashier';
        $validated['is_active'] = true;

        User::query()->create($validated);

        return redirect()->route('admin.users')->with('success', 'Akun kasir berhasil dibuat.');
    }

    public function updateCashier(Request $request, int $id): RedirectResponse
    {
        $cashier = User::query()->where('role', 'cashier')->findOrFail($id);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($cashier->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $cashier->update($validated);

        return redirect()->route('admin.users')->with('success', 'Akun kasir berhasil diperbarui.');
    }

    private function reportPeriod(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $startDate = Carbon::parse($validated['start_date'] ?? now()->startOfMonth())->startOfDay();
        $endDate = Carbon::parse($validated['end_date'] ?? now())->endOfDay();

        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => ['Tanggal akhir harus sama atau sesudah tanggal awal.'],
            ]);
        }

        return [$startDate, $endDate];
    }

    private function paidOrdersBetween(Carbon $startDate, Carbon $endDate): Builder
    {
        return Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate]);
    }
}

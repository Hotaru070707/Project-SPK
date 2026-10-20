<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\TopsisResult;
use App\Models\User;
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

    public function storeProduct(Request $request): RedirectResponse
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

        DB::transaction(function () use ($validated): void {
            $product = Product::query()->create($validated);
            if ($product->stock > 0) {
                StockMovement::query()->create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'in',
                    'qty' => $product->stock,
                    'stock_before' => 0,
                    'stock_after' => $product->stock,
                    'reason' => 'Stok awal saat produk dibuat',
                ]);
            }
        });

        return redirect()->route('admin.products')->with('success', 'Produk sembako berhasil ditambahkan!');
    }

    public function updateProduct(Request $request, int $id): RedirectResponse
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

        DB::transaction(function () use ($product, $validated): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $stockBefore = $lockedProduct->stock;
            $stockAfter = (int) $validated['stock'];
            $reason = $validated['stock_change_reason'] ?? null;
            if ($stockAfter !== $stockBefore && blank($reason)) {
                throw ValidationException::withMessages([
                    'stock_change_reason' => ['Alasan wajib diisi jika stok produk diubah.'],
                ]);
            }

            unset($validated['stock_change_reason']);
            $lockedProduct->update($validated);

            if ($stockAfter !== $stockBefore) {
                StockMovement::query()->create([
                    'product_id' => $lockedProduct->id,
                    'user_id' => auth()->id(),
                    'type' => 'adjustment',
                    'qty' => $stockAfter - $stockBefore,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reason' => $reason,
                ]);
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

    public function orders(): View
    {
        $orders = Order::with(['customer', 'items.product'])->latest()->paginate(15);

        return view('owner.orders.index', compact('orders'));
    }

    public function verifyOrder(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);
        $action = $request->input('action'); // approve / reject

        if ($action === 'approve') {
            $order->update([
                'payment_status' => 'paid',
                'order_status' => 'processing',
                'paid_at' => now(),
            ]);
            $msg = "Pesanan #{$order->order_number} berhasil disetujui & diverifikasi LUNAS!";
        } else {
            $order->update([
                'payment_status' => 'unpaid',
                'order_status' => 'cancelled',
            ]);
            $msg = "Pesanan #{$order->order_number} ditolak.";
        }

        return redirect()->back()->with('success', $msg);
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
            ->with(['product', 'user'])
            ->latest()
            ->paginate(20);

        return view('owner.stock.index', compact('products', 'movements'));
    }

    public function storeStockMovement(Request $request): RedirectResponse
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

        DB::transaction(function () use ($validated): void {
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

            $product->update(['stock' => $stockAfter]);
            StockMovement::query()->create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => $validated['type'],
                'qty' => $validated['type'] === 'adjustment'
                    ? $stockAfter - $stockBefore
                    : $validated['qty'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $validated['reason'],
            ]);
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashierController extends Controller
{
    public function index()
    {
        $todayOrders = Order::where('cashier_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->get();

        $totalOmsetToday = $todayOrders->where('payment_status', 'paid')->sum('total');
        $totalTrxToday = $todayOrders->count();

        return view('cashier.index', compact('totalOmsetToday', 'totalTrxToday', 'todayOrders'));
    }

    public function pos()
    {
        $products = Product::where('is_active', true)->with('category')->get();
        $members = User::where('role', 'customer')->get();

        return view('cashier.pos', compact('products', 'members'));
    }

    public function storeTransaction(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'payment_method' => 'required|in:cash,midtrans_qris,manual_transfer',
            'subtotal' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'total' => 'required|numeric',
            'cash_given' => 'nullable|numeric',
        ]);

        $orderNumber = 'POS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_number' => $orderNumber,
                'type' => 'offline',
                'cashier_id' => Auth::id(),
                'customer_id' => $request->customer_id ?: null,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'total' => $request->total,
                'payment_method' => $request->payment_method,
                'payment_status' => 'paid',
                'order_status' => 'completed',
                'paid_at' => now(),
            ]);

            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'price' => $item['price'],
                    'cost_price' => $item['cost_price'] ?? 0,
                    'qty' => $item['qty'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);

                // Kurangi stok fisik
                $product = Product::find($item['id']);
                if ($product) {
                    $product->decrement('stock', $item['qty']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'change' => max(0, ($request->cash_given ?? $order->total) - $order->total),
                'message' => 'Transaksi POS berhasil disimpan dan struk siap dicetak.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function transactions()
    {
        $orders = Order::where('type', 'offline')
            ->with(['items', 'customer'])
            ->latest()
            ->paginate(15);

        return view('cashier.transactions', compact('orders'));
    }
}

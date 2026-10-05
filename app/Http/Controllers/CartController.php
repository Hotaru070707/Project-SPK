<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }

        return view('cart.index', compact('cart', 'subtotal'));
    }

    public function add(Request $request)
    {
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
        session()->put('cart_count', array_sum(array_column($cart, 'qty')));

        return redirect()->back()->with('success', "{$product->name} berhasil ditambahkan ke keranjang!");
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

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }

        return view('cart.checkout', compact('cart', 'subtotal'));
    }

    public function processCheckout(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong.');
        }

        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:manual_transfer,midtrans_qris,cash',
        ]);

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }

        // Apply member discount if logged in
        $discount = 0;
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->member_tier === 'gold') {
                $discount = $subtotal * 0.05; // 5%
            } elseif ($user->member_tier === 'silver') {
                $discount = $subtotal * 0.03; // 3%
            }
        }

        $total = max(0, $subtotal - $discount);
        $orderNumber = 'ONL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $orderNotes = "Pemesan: {$request->recipient_name} ({$request->phone})";
        if (!empty($request->notes)) {
            $orderNotes .= " | Catatan: {$request->notes}";
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_number' => $orderNumber,
                'type' => 'online',
                'customer_id' => Auth::id(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'cash' ? 'pending' : 'unpaid',
                'order_status' => 'pending_payment',
                'notes' => $orderNotes,
            ]);

            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'price' => $item['price'],
                    'cost_price' => $item['cost_price'] ?? 0,
                    'qty' => $item['qty'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);

                // Kurangi stok produk
                $product = Product::find($item['id']);
                if ($product) {
                    $product->decrement('stock', $item['qty']);
                }
            }

            DB::commit();

            // Clear Cart
            session()->forget('cart');
            session()->forget('cart_count');

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', "Pesanan #{$order->order_number} berhasil dibuat!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }
}

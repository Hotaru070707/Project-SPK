<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function show($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->with(['items.product', 'customer'])->firstOrFail();
        return view('orders.show', compact('order'));
    }

    public function uploadProof(Request $request, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:3072',
        ]);

        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('payment_proofs', 'public');
            $order->update([
                'payment_proof' => $path,
                'payment_status' => 'pending', // Menunggu verifikasi admin
            ]);
        }

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah! Admin akan segera memverifikasi pesanan Anda.');
    }

    public function simulatePaid($orderNumber)
    {
        // Fitur simulasi bayar instan untuk demo/testing
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $order->update([
            'payment_status' => 'paid',
            'order_status' => 'processing',
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Simulasi pembayaran sukses! Status pesanan kini telah LUNAS (PAID).');
    }
}

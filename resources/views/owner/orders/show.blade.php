@extends('layouts.owner', ['title' => 'Detail Pesanan '.$order->order_number.' - Mobeli', 'header' => 'Detail Pesanan'])

@section('content')
@php
    $paymentMethodLabels = [
        'cash' => 'Tunai',
        'manual_transfer' => 'Transfer Manual',
        'midtrans_qris' => 'Midtrans QRIS',
        'midtrans_va' => 'Midtrans VA',
        'midtrans_ewallet' => 'Midtrans E-Wallet',
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
    $canConfirmTransfer = $order->type === 'online'
        && $order->payment_method === 'manual_transfer'
        && in_array($order->payment_status, ['unpaid', 'pending'], true)
        && $order->order_status === 'pending_payment';
@endphp

<div class="mx-auto max-w-6xl space-y-6">
    <a href="{{ route('admin.orders') }}" class="inline-flex items-center gap-2 text-xs font-bold text-neutral-600 hover:text-emerald-700 dark:text-slate-300 dark:hover:text-emerald-400">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Kembali ke daftar pesanan
    </a>

    <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900 sm:p-7">
        <div class="flex flex-col justify-between gap-5 md:flex-row md:items-start">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Invoice {{ strtoupper($order->type) }}</p>
                <h1 class="mt-1 break-all font-mono text-2xl font-black text-neutral-900 dark:text-white">{{ $order->order_number }}</h1>
                <p class="mt-2 text-xs text-neutral-500 dark:text-slate-400">Dibuat {{ $order->created_at->format('d M Y, H:i') }} · Diperbarui {{ $order->updated_at->format('d M Y, H:i') }}</p>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-neutral-200 p-3 dark:border-slate-700">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500 dark:text-slate-400">Status pembayaran</p>
                    <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ in_array($order->payment_status, ['expired', 'refunded'], true) ? 'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300' : ($order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300') }}">
                        {{ $order->payment_status_label }}
                    </span>
                </div>
                <div class="rounded-xl border border-neutral-200 p-3 dark:border-slate-700">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500 dark:text-slate-400">Status pesanan</p>
                    <span class="mt-2 inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">{{ $orderStatusLabels[$order->order_status] ?? strtoupper($order->order_status) }}</span>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <h2 class="flex items-center gap-2 text-sm font-bold text-neutral-900 dark:text-white">
                <i data-lucide="user-round" class="h-4 w-4 text-emerald-600"></i> Informasi Transaksi
            </h2>
            <dl class="mt-4 space-y-3 text-xs">
                <div><dt class="text-neutral-500 dark:text-slate-400">Pelanggan</dt><dd class="mt-0.5 font-semibold text-neutral-900 dark:text-white">{{ $order->customer?->name ?? ($order->type === 'offline' ? 'Pelanggan POS' : 'Pelanggan umum') }}</dd></div>
                @if($order->customer?->email)
                    <div><dt class="text-neutral-500 dark:text-slate-400">Email</dt><dd class="mt-0.5 break-all text-neutral-800 dark:text-slate-200">{{ $order->customer->email }}</dd></div>
                @endif
                @if($order->customer?->phone)
                    <div><dt class="text-neutral-500 dark:text-slate-400">Telepon akun</dt><dd class="mt-0.5 text-neutral-800 dark:text-slate-200">{{ $order->customer->phone }}</dd></div>
                @endif
                @if($order->cashier)
                    <div><dt class="text-neutral-500 dark:text-slate-400">Kasir</dt><dd class="mt-0.5 text-neutral-800 dark:text-slate-200">{{ $order->cashier->name }}</dd></div>
                @endif
                <div><dt class="text-neutral-500 dark:text-slate-400">Metode pembayaran</dt><dd class="mt-0.5 font-semibold text-neutral-800 dark:text-slate-200">{{ $paymentMethodLabels[$order->payment_method] ?? strtoupper($order->payment_method) }}</dd></div>
                @if($order->paid_at)
                    <div><dt class="text-neutral-500 dark:text-slate-400">Pembayaran tercatat</dt><dd class="mt-0.5 text-neutral-800 dark:text-slate-200">{{ $order->paid_at->format('d M Y, H:i') }}</dd></div>
                @endif
                @if($order->payment_reviewed_at)
                    <div><dt class="text-neutral-500 dark:text-slate-400">Dikonfirmasi oleh</dt><dd class="mt-0.5 text-neutral-800 dark:text-slate-200">{{ $order->paymentReviewer?->name ?? 'Akun tidak tersedia' }} · {{ $order->payment_reviewed_at->format('d M Y, H:i') }}</dd></div>
                @endif
            </dl>
        </section>

        <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900 xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="flex items-center gap-2 text-sm font-bold text-neutral-900 dark:text-white">
                            <i data-lucide="wallet-cards" class="h-4 w-4 text-emerald-600"></i> Pembayaran
                    </h2>
                    <p class="mt-1 text-xs text-neutral-500 dark:text-slate-400">Status pesanan dan pembayaran dicatat terpisah.</p>
                </div>

            @if($canConfirmTransfer)
                <div class="mt-5 space-y-3 border-t border-neutral-100 pt-4 dark:border-slate-800">
                    <p class="rounded-xl bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-950/30 dark:text-amber-200">
                        Konfirmasi hanya setelah pembayaran dan nominalnya dipastikan pada mutasi rekening.
                    </p>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <form action="{{ route('admin.orders.payment.confirm', $order) }}" method="POST" class="flex-1" onsubmit="return confirm('Pastikan pembayaran sudah masuk dan nominalnya sesuai pada mutasi rekening. Konfirmasi pesanan ini sebagai lunas?')">
                            @csrf
                            <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-700">Konfirmasi Pembayaran</button>
                        </form>
                        <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" class="flex-1" onsubmit="return confirm('Batalkan pesanan ini dan lepaskan reservasi stoknya?')">
                            @csrf
                            <button type="submit" class="w-full rounded-xl border border-red-200 px-4 py-2.5 text-xs font-bold text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950/40">Batalkan Pesanan</button>
                        </form>
                    </div>
                </div>
            @elseif($order->payment_method === 'midtrans_qris')
                <p class="mt-5 rounded-xl bg-blue-50 p-3 text-xs text-blue-800 dark:bg-blue-950/30 dark:text-blue-200">Pembayaran QRIS diperbarui otomatis oleh notifikasi Midtrans tervalidasi. Tidak tersedia persetujuan lunas manual.</p>
            @endif
        </section>
    </div>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-neutral-200 bg-neutral-50 p-4 dark:border-slate-800 dark:bg-slate-800/70">
            <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-slate-200">Rincian Barang</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-xs">
                <thead class="text-neutral-500 dark:text-slate-400"><tr><th class="p-3">Produk</th><th class="p-3 text-right">Harga</th><th class="p-3 text-center">Jumlah</th><th class="p-3 text-right">Subtotal</th></tr></thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-800">
                    @foreach($order->items as $item)
                        <tr>
                            <td class="p-3 font-semibold text-neutral-900 dark:text-white">{{ $item->product_name }}</td>
                            <td class="whitespace-nowrap p-3 text-right">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                            <td class="p-3 text-center">{{ $item->qty }}</td>
                            <td class="whitespace-nowrap p-3 text-right font-bold">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-neutral-200 bg-neutral-50 dark:border-slate-800 dark:bg-slate-800/50">
                    <tr><td colspan="3" class="p-3 text-right text-neutral-600 dark:text-slate-300">Subtotal</td><td class="p-3 text-right font-mono">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td></tr>
                    <tr><td colspan="3" class="p-3 text-right text-neutral-600 dark:text-slate-300">Diskon</td><td class="p-3 text-right font-mono">Rp {{ number_format((float) $order->discount, 0, ',', '.') }}</td></tr>
                    <tr class="font-bold text-neutral-900 dark:text-white"><td colspan="3" class="p-3 text-right">Total pembayaran</td><td class="p-3 text-right font-mono">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td></tr>
                </tfoot>
            </table>
        </div>
        @if($order->notes)
            <div class="border-t border-neutral-100 p-4 dark:border-slate-800">
                <p class="text-[10px] font-bold uppercase tracking-wide text-neutral-500 dark:text-slate-400">Catatan transaksi</p>
                <p class="mt-1 whitespace-pre-line text-xs text-neutral-700 dark:text-slate-300">{{ $order->notes }}</p>
            </div>
        @endif
    </section>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-neutral-200 bg-neutral-50 p-4 dark:border-slate-800 dark:bg-slate-800/70">
            <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-slate-200">Mutasi Stok Terkait Pesanan</h2>
        </div>
        @if($order->stockMovements->isEmpty())
            <p class="p-5 text-xs text-neutral-500 dark:text-slate-400">Tidak ada mutasi stok yang tertaut pada pesanan ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-left text-xs">
                    <thead class="text-neutral-500 dark:text-slate-400"><tr><th class="p-3">Waktu</th><th class="p-3">Produk</th><th class="p-3">Jenis</th><th class="p-3 text-center">Sebelum → Sesudah</th><th class="p-3">Alasan</th></tr></thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-slate-800">
                        @foreach($order->stockMovements as $movement)
                            <tr>
                                <td class="whitespace-nowrap p-3">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                <td class="p-3">{{ $movement->product?->name ?? 'Produk dihapus' }}</td>
                                <td class="p-3">{{ strtoupper($movement->type) }} ({{ $movement->qty > 0 ? '+' : '' }}{{ $movement->qty }})</td>
                                <td class="p-3 text-center font-mono">{{ $movement->stock_before }} → {{ $movement->stock_after }}</td>
                                <td class="p-3">{{ $movement->reason ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <p class="border-t border-neutral-100 p-3 text-[10px] text-neutral-400 dark:border-slate-800 dark:text-slate-500">Riwayat perubahan status pesanan terperinci belum tersedia pada skema saat ini.</p>
    </section>
</div>
@endsection

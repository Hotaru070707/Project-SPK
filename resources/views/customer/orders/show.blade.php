@extends('layouts.app', ['title' => 'Detail Pesanan #' . $order->order_number . ' - Mobeli'])

@section('content')
<div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-neutral-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs space-y-6">
        <!-- Order Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-neutral-200 dark:border-slate-800 gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider font-mono">Invoice</span>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $order->payment_status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' : 'bg-orange-100 dark:bg-orange-950/60 text-orange-800 dark:text-orange-300' }}">
                        Pembayaran: {{ $paymentStatusLabels[$order->payment_status] ?? strtoupper($order->payment_status) }}
                    </span>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">
                        {{ $orderStatusLabels[$order->order_status] ?? strtoupper($order->order_status) }}
                    </span>
                </div>
                <h1 class="text-2xl font-bold font-heading text-neutral-900 dark:text-white tracking-tight font-mono">
                    #{{ $order->order_number }}
                </h1>
                <p class="text-xs text-neutral-500 dark:text-slate-400 mt-1">Dibuat pada {{ $order->created_at->format('d M Y, H:i') }} WIB</p>
            </div>

            <!-- Action Buttons: Print Struk -->
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="px-3.5 py-2 bg-neutral-100 dark:bg-slate-800 hover:bg-neutral-200 dark:hover:bg-slate-700 text-neutral-800 dark:text-slate-200 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak Struk
                </button>
            </div>
        </div>

        @if($order->address)
            <div class="p-4 rounded-2xl bg-neutral-50 dark:bg-slate-800/80 border border-neutral-200 dark:border-slate-700 text-xs flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 flex items-center justify-center shrink-0">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                </div>
                <div class="space-y-0.5">
                    <strong class="text-neutral-900 dark:text-white font-bold block text-xs">Alamat Penerima: {{ $order->address->recipient_name }}</strong>
                    <p class="text-neutral-600 dark:text-slate-300 text-[11px]">{{ $order->address->full_address }}@if($order->address->city), {{ $order->address->city }}@endif @if($order->address->postal_code){{ $order->address->postal_code }}@endif</p>
                </div>
            </div>
        @elseif($order->order_status === 'shipped')
            <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs flex items-start gap-3">
                <i data-lucide="truck" class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0"></i>
                <div>
                    <strong class="text-blue-950 dark:text-blue-200">Pesanan dalam pengiriman</strong>
                    @if($order->shipping_tracking)
                        <p class="mt-1 text-blue-800 dark:text-blue-300">Nomor pelacakan: {{ $order->shipping_tracking }}</p>
                    @endif
                </div>
            </div>
        @else
            <!-- Banner Lokasi Pengambilan Toko -->
            <div class="p-4 rounded-2xl bg-neutral-50 dark:bg-slate-800/80 border border-neutral-200 dark:border-slate-700 text-xs flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                <i data-lucide="store" class="w-4 h-4"></i>
            </div>
            <div class="space-y-0.5">
                <strong class="text-neutral-900 dark:text-white font-bold block text-xs">Lokasi Pengambilan: Toko Kelontong Mobeli</strong>
                <p class="text-neutral-600 dark:text-slate-300 text-[11px]">Jl. Merdeka No. 45, Sukajadi • Jam Operasional: Setiap Hari, 07:00 - 21:00 WIB</p>
                @if($order->notes)
                    <p class="text-emerald-700 dark:text-emerald-400 text-[11px] font-medium pt-1">
                        📋 {{ $order->notes }}
                    </p>
                @endif
            </div>
            </div>
        @endif

        <!-- Payment Instructions Banner -->
        @if($order->payment_status === 'expired')
            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pembayaran Kedaluwarsa</h3>
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Pembayaran untuk pesanan ini telah kedaluwarsa. Periksa status pesanan atau hubungi toko untuk informasi lebih lanjut.</p>
            </div>
        @elseif($order->payment_status === 'refunded')
            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pembayaran Dikembalikan</h3>
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Status transaksi mencatat pembayaran telah dikembalikan.</p>
            </div>
        @elseif($order->payment_status === 'paid' && in_array($order->order_status, ['cancelled', 'void'], true))
            <div class="p-5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/80">
                <h3 class="text-sm font-bold text-amber-950 dark:text-amber-200">Pesanan Dibatalkan</h3>
                <p class="mt-1 text-xs text-amber-800 dark:text-amber-300">Pembayaran tercatat berhasil, tetapi status pesanan telah dibatalkan. Silakan hubungi toko untuk tindak lanjut.</p>
            </div>
        @elseif($order->payment_method === 'cash' && $order->payment_status !== 'paid')
            <!-- Bayar Tunai di Kasir Toko -->
            <div class="p-5 rounded-2xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-800/80 space-y-3">
                <div class="flex items-start gap-3">
                    <i data-lucide="banknote" class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5"></i>
                    <div>
                        <h3 class="text-sm font-bold text-blue-950 dark:text-blue-200">Bayar Tunai di Kasir Toko</h3>
                        <p class="text-xs text-blue-800 dark:text-blue-300 mt-0.5 leading-relaxed">
                            Pesanan menunggu pembayaran. Silakan datang ke Toko Mobeli, tunjukkan nomor pesanan <strong class="font-mono text-neutral-900 dark:text-white">#{{ $order->order_number }}</strong>, dan bayar tunai sebesar <strong class="font-bold price-font text-neutral-900 dark:text-white">Rp {{ number_format($order->total, 0, ',', '.') }}</strong> langsung di kasir toko.
                        </p>
                    </div>
                </div>
            </div>
        @elseif($order->payment_method === 'midtrans_qris' && $order->payment_status !== 'paid')
            <div class="p-5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/80 space-y-4">
                <div class="flex items-start gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                    <div>
                        <h3 class="text-sm font-bold text-amber-950 dark:text-amber-200">Menunggu Pembayaran QRIS</h3>
                        <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5 leading-relaxed">
                            Selesaikan pembayaran sebesar <strong class="font-bold price-font text-neutral-900 dark:text-white">Rp {{ number_format($order->total, 0, ',', '.') }}</strong> melalui halaman Midtrans. Status pesanan akan diperbarui otomatis setelah pembayaran terkonfirmasi.
                        </p>
                    </div>
                </div>

                @if(data_get($order->midtrans_payload, 'redirect_url'))
                    <a href="{{ data_get($order->midtrans_payload, 'redirect_url') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition">
                        <i data-lucide="qr-code" class="w-4 h-4"></i> Lanjutkan Pembayaran QRIS
                    </a>
                @endif
            </div>
        @elseif($order->payment_status !== 'paid')
            <div class="p-5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/80">
                <h3 class="text-sm font-bold text-amber-950 dark:text-amber-200">Menunggu Pembayaran Tunai</h3>
                <p class="text-xs text-amber-800 dark:text-amber-300 mt-1">Silakan bayar Rp {{ number_format($order->total, 0, ',', '.') }} langsung ke kasir saat mengambil pesanan.</p>
            </div>
        @else
            <!-- Pembayaran Lunas -->
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 text-xs flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                <div>
                    <span class="font-bold">Pembayaran Telah Diterima (Lunas)</span>
                    <p class="text-emerald-700 dark:text-emerald-300 mt-0.5">
                        Pesanan Anda sedang disiapkan oleh toko. Silakan datang ke Toko Mobeli dan tunjukkan nomor invoice/struk ini ke kasir untuk mengambil barang belanjaan Anda.
                    </p>
                </div>
            </div>
        @endif

        <section class="rounded-2xl border border-neutral-200 dark:border-slate-800 p-5 sm:p-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-slate-300">Perkembangan Pesanan</h2>
            <ol class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-neutral-900 dark:text-white">Pesanan dibuat</p>
                        <p class="mt-1 text-xs text-neutral-500 dark:text-slate-400">{{ $order->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : (in_array($order->payment_status, ['expired', 'refunded'], true) ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300') }}">
                        <i data-lucide="{{ $order->payment_status === 'paid' ? 'check' : (in_array($order->payment_status, ['expired', 'refunded'], true) ? 'circle-alert' : 'clock-3') }}" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-neutral-900 dark:text-white">
                            {{ $order->payment_status === 'paid' ? 'Pembayaran berhasil' : 'Status pembayaran' }}
                        </p>
                        <p class="mt-1 text-xs text-neutral-500 dark:text-slate-400">
                            {{ $paymentStatusLabels[$order->payment_status] ?? strtoupper($order->payment_status) }}
                            @if($order->paid_at)
                                · {{ $order->paid_at->format('d M Y, H:i') }}
                            @endif
                        </p>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $order->order_status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : (in_array($order->order_status, ['cancelled', 'void'], true) ? 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300') }}">
                        <i data-lucide="{{ $order->order_status === 'completed' ? 'check' : (in_array($order->order_status, ['cancelled', 'void'], true) ? 'x' : 'package-check') }}" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-neutral-900 dark:text-white">Status pesanan saat ini</p>
                        <p class="mt-1 text-xs text-neutral-500 dark:text-slate-400">{{ $orderStatusLabels[$order->order_status] ?? strtoupper($order->order_status) }}</p>
                    </div>
                </li>
            </ol>
            <p class="mt-4 text-[11px] text-neutral-400 dark:text-slate-500">Status dimuat dari transaksi tersimpan. Riwayat waktu untuk setiap perubahan status tidak tersedia.</p>
        </section>

        <!-- Order Items Table -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-slate-300 mb-3">Rincian Barang Belanjaan</h3>
            <div class="border border-neutral-200 dark:border-slate-800 rounded-xl overflow-hidden">
                <table class="w-full text-xs text-left">
                    <thead class="bg-neutral-50 dark:bg-slate-800 border-b border-neutral-200 dark:border-slate-700 text-neutral-600 dark:text-slate-400 uppercase font-semibold">
                        <tr>
                            <th class="p-3">Nama Produk</th>
                            <th class="p-3 text-right">Harga</th>
                            <th class="p-3 text-center">Qty</th>
                            <th class="p-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-slate-800">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="p-3 font-bold text-neutral-900 dark:text-white">
                                    <div class="flex items-center gap-2.5">
                                        @if($item->product?->image_url)
                                            <img src="{{ $item->product->image_url }}" alt="" class="w-9 h-9 rounded-lg object-cover border border-neutral-200 dark:border-slate-700">
                                        @endif
                                        <span>{{ $item->product_name }}</span>
                                    </div>
                                </td>
                                <td class="p-3 text-right price-font text-neutral-800 dark:text-slate-200">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="p-3 text-center font-bold text-neutral-800 dark:text-slate-200">{{ $item->qty }}</td>
                                <td class="p-3 text-right font-bold price-font text-neutral-900 dark:text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-neutral-50/70 dark:bg-slate-800/70 border-t border-neutral-200 dark:border-slate-700 font-bold">
                        <tr>
                            <td colspan="3" class="p-3 text-right text-neutral-600 dark:text-slate-400">Subtotal Barang:</td>
                            <td class="p-3 text-right price-font text-neutral-800 dark:text-slate-200">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if($order->discount > 0)
                            <tr class="text-emerald-700 dark:text-emerald-400">
                                <td colspan="3" class="p-3 text-right">Diskon:</td>
                                <td class="p-3 text-right price-font">- Rp {{ number_format($order->discount, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="p-3 text-right text-neutral-600 dark:text-slate-400">Pengambilan:</td>
                            <td class="p-3 text-right text-emerald-700 dark:text-emerald-400 font-semibold">Ambil di Toko (Bebas Biaya)</td>
                        </tr>
                        <tr class="text-sm text-neutral-900 dark:text-white border-t border-neutral-200 dark:border-slate-700">
                            <td colspan="3" class="p-3 text-right font-extrabold">Total Bayar:</td>
                            <td class="p-3 text-right font-extrabold text-emerald-800 dark:text-emerald-400 price-font">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="pt-4 border-t border-neutral-100 dark:border-slate-800 flex items-center justify-between text-xs">
            <a href="{{ url('/produk') }}" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                ← Kembali ke Katalog Sembako
            </a>
            <span class="text-neutral-400 dark:text-slate-500">Metode: {{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</span>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app', ['title' => 'Checkout Pesanan - Mobeli'])

@section('content')
<div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 pb-4 border-b border-neutral-200 dark:border-slate-800">
        <h1 class="text-2xl sm:text-3xl font-bold font-heading text-neutral-900 dark:text-white tracking-tight">Checkout Pesanan</h1>
        <p class="text-xs sm:text-sm text-neutral-500 dark:text-slate-400 mt-1">Pesan online lewat sistem, lalu ambil belanjaan Anda langsung di Toko Mobeli tanpa antri.</p>
    </div>

    <form action="{{ route('cart.checkout.process') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Form Pengambilan & Pembayaran -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Informasi Pengambilan di Toko -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-neutral-200 dark:border-slate-800 p-6 shadow-xs space-y-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-bold font-heading text-neutral-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="store" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i> Informasi Pengambilan di Toko (Self Pick-up)
                        </h2>
                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-[10px] font-bold">
                            Ambil di Toko
                        </span>
                    </div>

                    <!-- Banner Lokasi Toko Pengambilan -->
                    <div class="p-4 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/80 text-xs flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                        </div>
                        <div class="space-y-1">
                            <strong class="font-bold text-emerald-950 dark:text-emerald-200 block text-xs">Lokasi Pengambilan: Toko Kelontong Mobeli</strong>
                            <p class="text-emerald-800 dark:text-emerald-400 text-[11px] leading-relaxed">
                                Jl. Merdeka No. 45, Sukajadi • Jam Buka: Setiap Hari, 07:00 - 21:00 WIB
                            </p>
                            <p class="text-[11px] text-emerald-700 dark:text-emerald-300 font-medium">
                                ℹ️ Pesanan akan segera disiapkan oleh kasir. Anda tinggal datang ke toko dan tunjukkan nomor invoice atau bukti transaksi.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-slate-300 mb-1">
                                Nama Pemesan (Yang Mengambil Barang) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="recipient_name" value="{{ auth()->user()->name ?? old('recipient_name') }}" required
                                   placeholder="Nama lengkap pemesan"
                                   class="w-full bg-neutral-50 dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white dark:focus:bg-slate-800 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-slate-300 mb-1">
                                Nomor WhatsApp / HP <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="phone" value="{{ auth()->user()->phone ?? old('phone') }}" required
                                   placeholder="Contoh: 081234567890"
                                   class="w-full bg-neutral-50 dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white dark:focus:bg-slate-800 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-slate-300 mb-1">
                            Catatan / Perkiraan Jam Ambil (Opsional)
                        </label>
                        <input type="text" name="notes" placeholder="Contoh: Diambil sekitar jam 15:00 sore ini"
                               class="w-full bg-neutral-50 dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white dark:focus:bg-slate-800 transition">
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-neutral-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
                    <h2 class="text-base font-bold font-heading text-neutral-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="credit-card" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i> Pilih Metode Pembayaran
                    </h2>

                    <div class="space-y-3">
                        <!-- Option 1: QRIS via Midtrans -->
                        <label class="flex items-center p-4 rounded-xl border border-neutral-200 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-500 cursor-pointer transition bg-neutral-50/50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-800">
                            <input type="radio" name="payment_method" value="midtrans_qris" checked class="w-4 h-4 text-primary focus:ring-primary/40">
                            <div class="ml-3.5 flex-1">
                                <span class="block text-xs font-bold text-neutral-900 dark:text-white">QRIS Otomatis via Midtrans</span>
                                <span class="block text-[11px] text-neutral-500 dark:text-slate-400 mt-0.5">
                                    Setelah konfirmasi, Anda akan diarahkan ke halaman pembayaran QRIS Midtrans.
                                </span>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-[10px] font-bold">QRIS</span>
                        </label>

                        <!-- Option 2: Bayar Tunai di Kasir Toko -->
                        <label class="flex items-center p-4 rounded-xl border border-neutral-200 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-500 cursor-pointer transition bg-neutral-50/50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-800">
                            <input type="radio" name="payment_method" value="cash" class="w-4 h-4 text-primary focus:ring-primary/40">
                            <div class="ml-3.5 flex-1">
                                <span class="block text-xs font-bold text-neutral-900 dark:text-white">Bayar Tunai di Kasir Toko (Cash)</span>
                                <span class="block text-[11px] text-neutral-500 dark:text-slate-400 mt-0.5">
                                    Bayar uang tunai langsung ke kasir toko saat Anda datang mengambil barang pesanan.
                                </span>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-neutral-200 dark:bg-slate-700 text-neutral-700 dark:text-slate-300 text-[10px] font-bold">CASH</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Order Review Sidebar -->
            <div class="lg:col-span-4 bg-white dark:bg-slate-900 rounded-2xl border border-neutral-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
                <h2 class="text-base font-bold font-heading text-neutral-900 dark:text-white pb-3 border-b border-neutral-100 dark:border-slate-800">
                    Item Pesanan
                </h2>

                <div class="divide-y divide-neutral-100 dark:divide-slate-800 max-h-60 overflow-y-auto pr-1">
                    @foreach($cart as $item)
                        <div class="py-2.5 flex justify-between items-center text-xs">
                            <div class="max-w-[70%]">
                                <p class="font-bold text-neutral-900 dark:text-white truncate">{{ $item['name'] }}</p>
                                <span class="text-[10px] text-neutral-400 dark:text-slate-500">{{ $item['qty'] }} × Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                            </div>
                            <span class="font-bold text-neutral-800 dark:text-slate-200 price-font">
                                Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="pt-3 border-t border-neutral-100 dark:border-slate-800 space-y-2 text-xs text-neutral-600 dark:text-slate-400">
                    <div class="flex justify-between">
                        <span>Subtotal Belanja</span>
                        <span class="font-bold text-neutral-900 dark:text-white price-font">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Pengambilan</span>
                        <span class="font-semibold text-emerald-700 dark:text-emerald-400">Ambil di Toko (Bebas Biaya)</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-neutral-100 dark:border-slate-800 flex justify-between items-baseline">
                    <span class="text-sm font-bold text-neutral-900 dark:text-white">Total Tagihan</span>
                    <span class="text-xl font-extrabold text-emerald-800 dark:text-emerald-400 price-font">
                        Rp {{ number_format($subtotal, 0, ',', '.') }}
                    </span>
                </div>

                <button type="submit" 
                        class="w-full py-3.5 bg-primary hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md shadow-emerald-600/20 hover:scale-[1.01] active:scale-[0.99] transition flex items-center justify-center gap-2 mt-4">
                    <i data-lucide="check" class="w-4 h-4"></i> Konfirmasi Pesanan Ambil di Toko
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

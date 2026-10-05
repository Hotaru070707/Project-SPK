@extends('layouts.app', ['title' => 'Keranjang Belanja - Mobeli'])

@section('content')
<div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 pb-4 border-b border-neutral-200">
        <h1 class="text-2xl sm:text-3xl font-bold font-heading text-neutral-900 tracking-tight">Keranjang Belanja Anda</h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-1">Periksa kembali daftar sembako sebelum melanjutkan ke pembayaran.</p>
    </div>

    @if(!empty($cart) && count($cart) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Cart Items List -->
            <div class="lg:col-span-8 bg-white rounded-2xl border border-neutral-200 overflow-hidden shadow-xs">
                <div class="divide-y divide-neutral-100">
                    @foreach($cart as $id => $item)
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-xl bg-neutral-100 flex items-center justify-center text-2xl shrink-0 overflow-hidden border border-neutral-200/60">
                                    @if(!empty($item['image']))
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                    @else
                                        🛒
                                    @endif
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider">
                                        {{ $item['category'] ?? 'Sembako' }}
                                    </span>
                                    <h3 class="text-sm font-bold text-neutral-900 leading-snug">
                                        {{ $item['name'] }}
                                    </h3>
                                    <div class="text-xs text-neutral-500 mt-0.5 price-font">
                                        Rp {{ number_format($item['price'], 0, ',', '.') }} / {{ $item['unit'] }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-6 pt-2 sm:pt-0 border-t sm:border-t-0 border-neutral-100">
                                <!-- Qty Update Form -->
                                <form action="{{ route('cart.update') }}" method="POST" class="flex items-center border border-neutral-200 rounded-lg overflow-hidden bg-neutral-50">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $id }}">
                                    <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepDown(); this.form.submit();" class="px-2.5 py-1 text-xs hover:bg-neutral-200 font-bold">-</button>
                                    <input type="number" name="qty" value="{{ $item['qty'] }}" min="1" onchange="this.form.submit()" 
                                           class="w-12 text-center text-xs font-bold bg-transparent border-0 focus:ring-0">
                                    <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepUp(); this.form.submit();" class="px-2.5 py-1 text-xs hover:bg-neutral-200 font-bold">+</button>
                                </form>

                                <!-- Subtotal per Item -->
                                <div class="text-right">
                                    <span class="text-sm font-extrabold text-neutral-900 price-font block">
                                        Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                                    </span>
                                </div>

                                <!-- Remove Form -->
                                <form action="{{ route('cart.remove') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $id }}">
                                    <button type="submit" class="p-1.5 text-neutral-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Hapus">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Summary Card -->
            <div class="lg:col-span-4 bg-white rounded-2xl border border-neutral-200 p-6 shadow-xs space-y-5">
                <h2 class="text-base font-bold font-heading text-neutral-900 pb-3 border-b border-neutral-100">
                    Ringkasan Belanja
                </h2>

                <div class="space-y-2.5 text-xs text-neutral-600">
                    <div class="flex justify-between">
                        <span>Total Harga Barang</span>
                        <span class="font-bold text-neutral-900 price-font">
                            Rp {{ number_format($subtotal, 0, ',', '.') }}
                        </span>
                    </div>

                    @auth
                        @if(auth()->user()->member_tier === 'gold')
                            <div class="flex justify-between text-emerald-700 font-medium">
                                <span>Diskon Gold Member (5%)</span>
                                <span class="price-font">- Rp {{ number_format($subtotal * 0.05, 0, ',', '.') }}</span>
                            </div>
                        @elseif(auth()->user()->member_tier === 'silver')
                            <div class="flex justify-between text-emerald-700 font-medium">
                                <span>Diskon Silver Member (3%)</span>
                                <span class="price-font">- Rp {{ number_format($subtotal * 0.03, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @endauth

                    <div class="flex justify-between">
                        <span>Metode Pengambilan</span>
                        <span class="font-semibold text-emerald-700 dark:text-emerald-400">Ambil di Toko (Bebas Biaya)</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-neutral-100 flex justify-between items-baseline">
                    <span class="text-sm font-bold text-neutral-900">Total Akhir</span>
                    <span class="text-xl font-extrabold text-emerald-800 price-font">
                        Rp {{ number_format($subtotal, 0, ',', '.') }}
                    </span>
                </div>

                <a href="{{ route('cart.checkout') }}" 
                   class="w-full py-3 px-4 bg-primary hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md shadow-emerald-600/20 hover:scale-[1.01] active:scale-[0.99] transition flex items-center justify-center gap-2">
                    Lanjut ke Checkout <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>

                <a href="{{ url('/produk') }}" class="block text-center text-xs font-semibold text-neutral-500 hover:text-primary transition">
                    ← Tambah Belanjaan Lain
                </a>
            </div>
        </div>
    @else
        <div class="py-16 text-center bg-white rounded-3xl border border-neutral-200 p-8 max-w-md mx-auto">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-neutral-100 text-neutral-400 flex items-center justify-center mb-4">
                <i data-lucide="shopping-cart" class="w-8 h-8"></i>
            </div>
            <h2 class="text-lg font-bold font-heading text-neutral-900">Keranjang Belanja Masih Kosong</h2>
            <p class="text-xs text-neutral-500 mt-1 mb-6">Pilih sembako dan kebutuhan dapur terbaik Anda sekarang.</p>
            <a href="{{ url('/produk') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-xs font-bold rounded-xl shadow-sm hover:bg-emerald-700 transition">
                <i data-lucide="store" class="w-4 h-4"></i> Mulai Belanja
            </a>
        </div>
    @endif
</div>
@endsection

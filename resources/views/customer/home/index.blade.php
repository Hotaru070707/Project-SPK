@extends('layouts.app', ['title' => 'Mobeli - Toko Kelontong Modern'])

@section('content')
<!-- Hero Section -->
<section class="relative overflow-hidden bg-gradient-to-b from-emerald-50/60 to-transparent pt-10 pb-16 sm:pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Left Headline -->
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                    Sistem Kasir & Toko Kelontong Cerdas
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold font-heading text-neutral-900 tracking-tight leading-[1.1]">
                    Belanja Kebutuhan Dapur Lengkap, Murah & Dekat.
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 leading-relaxed max-w-xl">
                    Sedia beras pulen, minyak goreng, telur, gula, dan sembako berkualitas untuk memenuhi kebutuhan harian Anda.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <a href="{{ url('/produk') }}" 
                       class="px-6 py-3.5 bg-primary hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-emerald-600/25 hover:scale-[1.02] active:scale-[0.98] transition flex items-center gap-2">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i> Belanja Sekarang
                    </a>

                    @auth
                        @if(auth()->user()->isCashier())
                            <a href="{{ url('/kasir/pos') }}" 
                               class="px-5 py-3.5 bg-white border border-neutral-200 hover:border-neutral-300 text-neutral-800 font-semibold text-sm rounded-xl hover:bg-neutral-50 transition flex items-center gap-2">
                                <i data-lucide="calculator" class="w-4 h-4 text-emerald-600"></i> Buka Kasir POS
                            </a>
                        @elseif(auth()->user()->isOwner())
                            <a href="{{ url('/admin') }}" 
                               class="px-5 py-3.5 bg-white border border-neutral-200 hover:border-neutral-300 text-neutral-800 font-semibold text-sm rounded-xl hover:bg-neutral-50 transition flex items-center gap-2">
                                <i data-lucide="shield" class="w-4 h-4 text-emerald-600"></i> Dashboard Owner
                            </a>
                        @else
                            <a href="{{ url('/tentang') }}" 
                               class="px-5 py-3.5 bg-white border border-neutral-200 hover:border-neutral-300 text-neutral-800 font-semibold text-sm rounded-xl hover:bg-neutral-50 transition flex items-center gap-2">
                                <i data-lucide="info" class="w-4 h-4 text-emerald-600"></i> Tentang Toko
                            </a>
                        @endif
                    @else
                        <a href="{{ url('/tentang') }}" 
                           class="px-5 py-3.5 bg-white border border-neutral-200 hover:border-neutral-300 text-neutral-800 font-semibold text-sm rounded-xl hover:bg-neutral-50 transition flex items-center gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-emerald-600"></i> Tentang Toko
                        </a>
                    @endauth
                </div>

                @auth
                <div class="pt-6 max-w-lg">
                    <form action="{{ url('/produk') }}" method="GET" class="relative flex items-center">
                        <i data-lucide="search" class="w-5 h-5 text-neutral-400 absolute left-4"></i>
                        <input type="text" name="q" placeholder="Cari beras, minyak, gula..." 
                               class="w-full bg-white border-2 border-neutral-200 rounded-xl pl-12 pr-24 py-3.5 text-sm font-medium focus:outline-none focus:border-primary/50 focus:ring-4 focus:ring-primary/10 transition shadow-sm">
                        <button type="submit" class="absolute right-2 top-2 bottom-2 px-4 bg-primary hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition">
                            Cari
                        </button>
                    </form>
                </div>
                @endauth

                <!-- Trust Points -->
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-neutral-200/80 text-xs text-neutral-600">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-primary"></i>
                        <span>100% Produk Asli</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="truck" class="w-4 h-4 text-primary"></i>
                        <span>Antar Cepat Area Lokal</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-primary"></i>
                        <span>Harga Terjangkau</span>
                    </div>
                </div>
            </div>

            <!-- Customer Benefits Card -->
            <div class="lg:col-span-5">
                <div class="relative bg-white rounded-3xl border border-neutral-200 shadow-xl p-6 sm:p-8 overflow-hidden">
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-emerald-100 rounded-full blur-2xl opacity-60"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between pb-4 border-b border-neutral-100 mb-5">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Belanja Praktis</span>
                                <h3 class="text-base font-bold font-heading text-neutral-900">Kebutuhan Harian Lebih Mudah</h3>
                            </div>
                            <i data-lucide="shopping-basket" class="h-6 w-6 text-emerald-600"></i>
                        </div>

                        <div class="space-y-4 text-xs text-neutral-600">
                            <div class="flex items-start gap-3">
                                <i data-lucide="package-check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"></i>
                                <div>
                                    <p class="font-bold text-neutral-900">Produk kebutuhan sehari-hari</p>
                                    <p class="mt-1">Temukan sembako dan keperluan rumah tangga dalam satu tempat.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <i data-lucide="truck" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"></i>
                                <div>
                                    <p class="font-bold text-neutral-900">Pesan dengan praktis</p>
                                    <p class="mt-1">Pilih produk dari katalog dan lanjutkan pesanan secara online.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <i data-lucide="heart-handshake" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"></i>
                                <div>
                                    <p class="font-bold text-neutral-900">Pelayanan ramah</p>
                                    <p class="mt-1">Kami siap membantu memenuhi kebutuhan belanja keluarga Anda.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-t border-neutral-100 flex items-center justify-between text-xs">
                            <span class="text-neutral-500">Pilihan Terbaik & Berkualitas</span>
                            <a href="{{ url('/produk') }}" class="font-bold text-emerald-600 hover:underline flex items-center gap-1">
                                Jelajahi Katalog <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Strip -->
<section class="py-8 bg-white border-y border-neutral-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold font-heading text-neutral-900">Kategori Pilihan</h2>
            <a href="{{ url('/produk') }}" class="text-xs font-semibold text-emerald-600 hover:underline flex items-center gap-1">
                Semua Kategori <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
            @foreach($categories as $cat)
                <a href="{{ url('/produk?kategori=' . $cat->slug) }}" 
                   class="p-4 rounded-xl border border-neutral-200 hover:border-emerald-500 hover:shadow-sm bg-neutral-50/50 hover:bg-white transition group text-center">
                    <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-emerald-100/70 text-emerald-700 flex items-center justify-center group-hover:scale-110 transition">
                        <i data-lucide="{{ $cat->icon ?? 'box' }}" class="w-5 h-5"></i>
                    </div>
                    <span class="block text-xs font-bold text-neutral-900 group-hover:text-emerald-700 transition">
                        {{ $cat->name }}
                    </span>
                    <span class="text-[10px] text-neutral-400">
                        {{ $cat->products_count }} produk
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between mb-8">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Produk Terlaris</span>
            <h2 class="text-2xl font-bold font-heading text-neutral-900 tracking-tight">Kebutuhan Pokok Favorit Pelanggan</h2>
        </div>
        <a href="{{ url('/produk') }}" class="text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1">
            Lihat Semua Produk ({{ $featuredProducts->count() }}+) <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        @foreach($featuredProducts as $product)
            <div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between group">
                <div class="p-4">
                    <!-- Category Badge & Stock Status -->
                    <div class="flex items-center justify-between gap-1 mb-3">
                        <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider">
                            {{ $product->category->name ?? 'Sembako' }}
                        </span>
                        @if($product->is_low_stock)
                            <span class="px-2 py-0.5 rounded-full bg-orange-100 text-orange-800 text-[10px] font-bold">
                                Sisa {{ $product->stock }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                Stok {{ $product->stock }}
                            </span>
                        @endif
                    </div>

                    <!-- Product Visual -->
                    <a href="{{ url('/produk/' . $product->slug) }}" class="block aspect-4/3 bg-neutral-100 rounded-xl mb-3 flex items-center justify-center text-4xl group-hover:scale-102 transition overflow-hidden">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            @if($product->category && $product->category->slug === 'sembako')
                                🌾
                            @elseif($product->category && $product->category->slug === 'minuman')
                                🧃
                            @elseif($product->category && $product->category->slug === 'bumbu-dapur')
                                🧂
                            @elseif($product->category && $product->category->slug === 'snack-camilan')
                                🍪
                            @else
                                🧼
                            @endif
                        @endif
                    </a>

                    <!-- Product Name -->
                    <h3 class="text-xs sm:text-sm font-bold text-neutral-900 line-clamp-2 group-hover:text-emerald-700 transition">
                        <a href="{{ url('/produk/' . $product->slug) }}">
                            {{ $product->name }}
                        </a>
                    </h3>
                </div>

                <div class="p-4 pt-0">
                    <div class="flex items-baseline justify-between mb-3">
                        <div>
                            <span class="text-[10px] text-neutral-400 block">Harga</span>
                            <span class="text-sm sm:text-base font-bold text-neutral-900 price-font">
                                Rp {{ number_format($product->sell_price, 0, ',', '.') }}
                            </span>
                        </div>
                        <span class="text-[11px] text-neutral-500">/ {{ $product->unit }}</span>
                    </div>

                    <!-- Direct Add to Cart / Details -->
                    <form action="{{ url('/akun/keranjang/tambah') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="qty" value="1">
                        <button type="submit" 
                                class="w-full py-2 bg-emerald-50 hover:bg-primary text-emerald-800 hover:text-white text-xs font-bold rounded-lg border border-emerald-200 hover:border-transparent transition flex items-center justify-center gap-1.5">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Masukkan Keranjang
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection

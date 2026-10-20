@extends('layouts.app', ['title' => 'Katalog Produk Sembako - Mobeli'])

@section('content')
<div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Page -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-neutral-200 pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold font-heading text-neutral-900 tracking-tight">Katalog Produk Toko</h1>
            <p class="text-xs sm:text-sm text-neutral-500 mt-1">Daftar kebutuhan sembako dan kebutuhan harian keluarga terlengkap.</p>
        </div>

        <!-- Sorting & Search in Header -->
        <form method="GET" action="{{ url('/produk') }}" class="flex flex-wrap items-center gap-2">
            @if(request('kategori'))
                <input type="hidden" name="kategori" value="{{ request('kategori') }}">
            @endif
            @auth
            <div class="relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama barang..."
                       class="bg-white border border-neutral-200 rounded-lg pl-8 pr-3 py-1.5 text-xs text-neutral-800 focus:outline-none focus:ring-2 focus:ring-primary/40">
                <i data-lucide="search" class="w-3.5 h-3.5 text-neutral-400 absolute left-2.5 top-2.5"></i>
            </div>
            @endauth
            <select name="sort" onchange="this.form.submit()" 
                    class="bg-white border border-neutral-200 rounded-lg px-2.5 py-1.5 text-xs text-neutral-800 focus:outline-none focus:ring-2 focus:ring-primary/40">
                <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Urut: Terbaru</option>
                <option value="termurah" {{ request('sort') == 'termurah' ? 'selected' : '' }}>Harga: Termurah</option>
                <option value="termahal" {{ request('sort') == 'termahal' ? 'selected' : '' }}>Harga: Termahal</option>
                <option value="stok_terbanyak" {{ request('sort') == 'stok_terbanyak' ? 'selected' : '' }}>Stok Terbanyak</option>
            </select>
        </form>
    </div>

    <!-- Category Filter Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6 text-xs no-scrollbar">
        <a href="{{ url('/produk') }}" 
           class="px-3.5 py-1.5 rounded-full font-semibold transition shrink-0 {{ !request('kategori') ? 'bg-primary text-white shadow-xs' : 'bg-white border border-neutral-200 text-neutral-600 hover:border-neutral-300' }}">
            Semua Produk
        </a>
        @foreach($categories as $cat)
            <a href="{{ url('/produk?kategori=' . $cat->slug . (request('q') ? '&q='.request('q') : '')) }}" 
               class="px-3.5 py-1.5 rounded-full font-semibold transition shrink-0 {{ request('kategori') == $cat->slug ? 'bg-primary text-white shadow-xs' : 'bg-white border border-neutral-200 text-neutral-600 hover:border-neutral-300' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <!-- Products Grid -->
    @if($products->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($products as $product)
                <div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between group">
                    <div class="p-4">
                        <div class="flex items-center justify-between gap-1 mb-2">
                            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider">
                                {{ $product->category->name ?? 'Sembako' }}
                            </span>
                            @if($product->is_low_stock)
                                <span class="px-2 py-0.5 rounded-full bg-orange-100 text-orange-800 text-[9px] font-bold">
                                    Sisa {{ $product->stock }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[9px] font-bold">
                                    Stok {{ $product->stock }}
                                </span>
                            @endif
                        </div>

                        <!-- Product Emoji / Thumbnail Placeholder -->
                        <a href="{{ url('/produk/' . $product->slug) }}" class="block aspect-4/3 bg-neutral-100/70 rounded-xl mb-3 flex items-center justify-center text-4xl group-hover:scale-102 transition overflow-hidden">
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
                            <span class="text-[10px] text-neutral-500">/ {{ $product->unit }}</span>
                        </div>

                        <form action="{{ url('/akun/keranjang/tambah') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" 
                                    class="w-full py-2 bg-emerald-50 hover:bg-primary text-emerald-800 hover:text-white text-xs font-bold rounded-lg border border-emerald-200 hover:border-transparent transition flex items-center justify-center gap-1">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Keranjang
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @else
        <div class="py-16 text-center bg-white rounded-2xl border border-neutral-200 p-8">
            <div class="w-12 h-12 mx-auto rounded-full bg-neutral-100 text-neutral-400 flex items-center justify-center mb-3">
                <i data-lucide="package-search" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base font-bold text-neutral-800">Produk Tidak Ditemukan</h3>
            <p class="text-xs text-neutral-500 mt-1">Coba gunakan kata kunci pencarian lain atau pilih kategori yang berbeda.</p>
            <a href="{{ url('/produk') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white text-xs font-bold rounded-lg">
                Reset Pencarian
            </a>
        </div>
    @endif
</div>
@endsection

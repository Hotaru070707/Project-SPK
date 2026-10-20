@extends('layouts.app', ['title' => $product->name . ' - Mobeli'])

@section('content')
<div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-6">
        <a href="{{ url('/') }}" class="hover:text-primary">Beranda</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-neutral-300"></i>
        <a href="{{ url('/produk') }}" class="hover:text-primary">Katalog</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-neutral-300"></i>
        <span class="text-neutral-900 font-medium truncate max-w-xs">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12 bg-white rounded-3xl border border-neutral-200 p-6 sm:p-8 shadow-xs">
        <!-- Visual Section -->
        <div class="md:col-span-5">
            <div class="aspect-square rounded-2xl bg-neutral-100 flex items-center justify-center text-7xl shadow-inner border border-neutral-200/60 overflow-hidden">
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-2xl shadow-xs">
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
            </div>

            <!-- Barcode & SKU Pill -->
            @if($product->barcode)
                <div class="mt-4 p-3 bg-neutral-50 rounded-xl border border-neutral-100 flex items-center justify-between text-xs font-mono">
                    <span class="text-neutral-400">Barcode POS</span>
                    <span class="text-neutral-800 font-bold tracking-wider">{{ $product->barcode }}</span>
                </div>
            @endif
        </div>

        <!-- Product Details & Buy Form -->
        <div class="md:col-span-7 space-y-5">
            <div>
                <span class="inline-block px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-2">
                    {{ $product->category->name }}
                </span>
                <h1 class="text-2xl sm:text-3xl font-bold font-heading text-neutral-900 tracking-tight">
                    {{ $product->name }}
                </h1>
            </div>

            <!-- Price -->
            <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100/80 flex items-baseline justify-between">
                <div>
                    <span class="text-xs text-neutral-500 block mb-0.5">Harga Jual Resmi</span>
                    <span class="text-3xl font-extrabold text-emerald-800 price-font">
                        Rp {{ number_format($product->sell_price, 0, ',', '.') }}
                    </span>
                    <span class="text-xs text-neutral-500">/ {{ $product->unit }}</span>
                </div>

                @if($product->is_low_stock)
                    <span class="px-2.5 py-1 rounded-full bg-orange-100 text-orange-800 text-xs font-bold">
                        Stok Menipis: Sisa {{ $product->stock }}
                    </span>
                @else
                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                        Stok Tersedia: {{ $product->stock }} {{ $product->unit }}
                    </span>
                @endif
            </div>

            <!-- Description -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-800 mb-2">Deskripsi Produk</h3>
                <p class="text-sm text-neutral-600 leading-relaxed">
                    {{ $product->description ?? 'Produk sembako berkualitas terjamin dari toko kelontong kami, siap diproses dan dikirim ke rumah Anda.' }}
                </p>
            </div>

            <!-- Quantity & Add to Cart -->
            <form action="{{ url('/akun/keranjang/tambah') }}" method="POST" class="pt-4 border-t border-neutral-100">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                
                <div class="flex items-center gap-4 mb-4">
                    <label class="text-xs font-bold uppercase tracking-wider text-neutral-700">Jumlah Beli</label>
                    <div class="flex items-center border border-neutral-200 rounded-lg overflow-hidden bg-neutral-50">
                        <button type="button" onclick="decrementQty()" class="px-3 py-1.5 hover:bg-neutral-200 text-neutral-700 font-bold">-</button>
                        <input type="number" id="qty" name="qty" value="1" min="1" max="{{ $product->stock }}" 
                               class="w-14 text-center text-sm font-bold bg-transparent border-0 focus:ring-0">
                        <button type="button" onclick="incrementQty({{ $product->stock }})" class="px-3 py-1.5 hover:bg-neutral-200 text-neutral-700 font-bold">+</button>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" 
                            class="flex-1 py-3 px-6 bg-primary hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md shadow-emerald-600/20 hover:scale-[1.01] active:scale-[0.99] transition flex items-center justify-center gap-2">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i> Masukkan ke Keranjang
                    </button>
                    <a href="{{ url('/produk') }}" 
                       class="py-3 px-4 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-semibold text-sm rounded-xl transition">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function incrementQty(max) {
        const input = document.getElementById('qty');
        let val = parseInt(input.value) || 1;
        if (val < max) input.value = val + 1;
    }
    function decrementQty() {
        const input = document.getElementById('qty');
        let val = parseInt(input.value) || 1;
        if (val > 1) input.value = val - 1;
    }
</script>
@endsection

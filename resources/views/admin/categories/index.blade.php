@extends('layouts.admin', ['title' => 'Kategori Sembako - Mobeli', 'header' => 'Kategori Produk Toko'])

@section('content')
<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
    <!-- List Categories -->
    <div class="md:col-span-8 bg-white rounded-2xl border border-neutral-200 overflow-hidden shadow-xs">
        <div class="p-4 bg-neutral-50 border-b border-neutral-200">
            <h3 class="font-bold text-xs uppercase tracking-wider text-neutral-700">Daftar Kategori</h3>
        </div>

        <div class="divide-y divide-neutral-100">
            @foreach($categories as $cat)
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-sm text-neutral-900">{{ $cat->name }}</h4>
                        <p class="text-xs text-neutral-500 mt-0.5">{{ $cat->description }}</p>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-lg font-mono">
                        {{ $cat->products_count }} Produk
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Add Category Form -->
    <div class="md:col-span-4 bg-white rounded-2xl border border-neutral-200 p-5 shadow-xs h-fit space-y-4">
        <h3 class="text-sm font-bold font-heading text-neutral-900">Tambah Kategori Baru</h3>
        <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-neutral-700 mb-1">Nama Kategori</label>
                <input type="text" name="name" required placeholder="Contoh: Bumbu Dapur"
                       class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block font-bold text-neutral-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3" placeholder="Keterangan singkat kategori..."
                          class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-sm transition">
                Simpan Kategori
            </button>
        </form>
    </div>
</div>
@endsection

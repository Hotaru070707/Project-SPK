@extends('layouts.owner', ['title' => 'Stok & Mutasi - Mobeli', 'header' => 'Pengelolaan Stok dan Riwayat Mutasi'])

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-neutral-200 p-5 shadow-xs">
        <h2 class="text-base font-bold font-heading text-neutral-900">Catat Pergerakan Stok</h2>
        <p class="mt-1 text-xs text-neutral-500">Stok masuk menambah persediaan, stok keluar mengurangi persediaan, dan penyesuaian menetapkan jumlah fisik hasil penghitungan.</p>

        <form action="{{ route('admin.stock.store') }}" method="POST" class="mt-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3 items-end">
            @csrf
            <div>
                <label for="product_id" class="mb-1 block text-xs font-semibold text-neutral-700">Produk</label>
                <select id="product_id" name="product_id" required class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs">
                    <option value="">Pilih produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                            {{ $product->name }} (stok {{ $product->stock }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="type" class="mb-1 block text-xs font-semibold text-neutral-700">Jenis mutasi</label>
                <select id="type" name="type" required class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs">
                    <option value="in" @selected(old('type') === 'in')>Stok masuk</option>
                    <option value="out" @selected(old('type') === 'out')>Stok keluar</option>
                    <option value="adjustment" @selected(old('type') === 'adjustment')>Penyesuaian stok fisik</option>
                </select>
            </div>
            <div>
                <label for="qty" class="mb-1 block text-xs font-semibold text-neutral-700">Jumlah / stok fisik</label>
                <input id="qty" name="qty" type="number" min="0" value="{{ old('qty', 1) }}" required class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs">
            </div>
            <div>
                <label for="reason" class="mb-1 block text-xs font-semibold text-neutral-700">Alasan</label>
                <input id="reason" name="reason" value="{{ old('reason') }}" maxlength="255" required placeholder="Contoh: pembelian dari pemasok" class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs">
            </div>
            <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white hover:bg-blue-700">Simpan Mutasi</button>
        </form>
        @error('qty') <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
        @error('product_id') <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs">
        <div class="border-b border-neutral-200 bg-neutral-50 p-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-700">Riwayat Mutasi Stok</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-neutral-100 text-neutral-600">
                    <tr>
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Produk</th>
                        <th class="p-3">Jenis</th>
                        <th class="p-3 text-right">Perubahan</th>
                        <th class="p-3 text-center">Sebelum → Sesudah</th>
                        <th class="p-3">Alasan</th>
                        <th class="p-3">Pengguna</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($movements as $movement)
                        <tr>
                            <td class="whitespace-nowrap p-3">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3 font-semibold text-neutral-900">{{ $movement->product->name ?? 'Produk dihapus' }}</td>
                            <td class="p-3">{{ match($movement->type) { 'in' => 'Masuk', 'out' => 'Keluar', 'return' => 'Retur', default => 'Penyesuaian' } }}</td>
                            <td class="p-3 text-right font-mono">{{ $movement->type === 'adjustment' ? ($movement->qty > 0 ? '+' : '') : ($movement->type === 'out' ? '-' : '+') }}{{ abs($movement->qty) }}</td>
                            <td class="p-3 text-center font-mono">{{ $movement->stock_before }} → {{ $movement->stock_after }}</td>
                            <td class="p-3">{{ $movement->reason ?: '-' }}</td>
                            <td class="p-3">{{ $movement->user->name ?? 'Sistem' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-8 text-center text-neutral-500">Belum ada pergerakan stok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-neutral-100 p-4">{{ $movements->links() }}</div>
    </div>
</div>
@endsection

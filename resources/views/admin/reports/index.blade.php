@extends('layouts.admin', ['title' => 'Laporan Toko - Mobeli', 'header' => 'Laporan Penjualan dan Persediaan'])

@section('content')
<div class="space-y-6">
    <form method="GET" action="{{ route('admin.reports') }}" class="flex flex-wrap items-end gap-3 rounded-2xl border border-neutral-200 bg-white p-4 shadow-xs">
        <div>
            <label for="start_date" class="mb-1 block text-xs font-semibold text-neutral-700">Dari tanggal</label>
            <input id="start_date" name="start_date" type="date" value="{{ $startDate->toDateString() }}" class="rounded-lg border border-neutral-300 px-3 py-2 text-xs">
        </div>
        <div>
            <label for="end_date" class="mb-1 block text-xs font-semibold text-neutral-700">Sampai tanggal</label>
            <input id="end_date" name="end_date" type="date" value="{{ $endDate->toDateString() }}" class="rounded-lg border border-neutral-300 px-3 py-2 text-xs">
        </div>
        <button class="rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white hover:bg-blue-700">Terapkan Filter</button>
        <a href="{{ route('admin.reports.export', ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()]) }}" class="rounded-lg border border-neutral-300 px-4 py-2 text-xs font-bold text-neutral-700 hover:bg-neutral-50">Ekspor CSV</a>
    </form>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-neutral-200 bg-white p-5">
            <span class="text-xs font-bold uppercase tracking-wider text-neutral-500">Omzet Transaksi Lunas</span>
            <p class="mt-2 font-mono text-xl font-black text-neutral-900">Rp {{ number_format($revenue, 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-neutral-500">{{ $totalTransactions }} transaksi pada periode ini</p>
        </div>
        <div class="rounded-2xl border border-neutral-200 bg-white p-5">
            <span class="text-xs font-bold uppercase tracking-wider text-neutral-500">Laba Kotor</span>
            <p class="mt-2 font-mono text-xl font-black text-blue-700">Rp {{ number_format($grossProfit, 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-neutral-500">Omzet dikurangi modal barang terjual; bukan omzet total.</p>
        </div>
        <div class="rounded-2xl border border-neutral-200 bg-white p-5">
            <span class="text-xs font-bold uppercase tracking-wider text-neutral-500">Produk Stok Kritis</span>
            <p class="mt-2 font-mono text-xl font-black text-orange-600">{{ $lowStockProducts->count() }} Produk</p>
            <p class="mt-1 text-[11px] text-neutral-500">Stok tersedia sama dengan atau di bawah minimum.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white">
            <div class="border-b border-neutral-200 bg-neutral-50 p-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-700">Produk Terlaris</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-neutral-500"><tr><th class="p-3">Produk</th><th class="p-3 text-right">Terjual</th><th class="p-3 text-right">Omzet</th><th class="p-3 text-right">Laba kotor</th></tr></thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse($topProducts as $product)
                            <tr><td class="p-3 font-semibold text-neutral-900">{{ $product->product_name }}</td><td class="p-3 text-right">{{ $product->units_sold }}</td><td class="p-3 text-right">Rp {{ number_format($product->revenue, 0, ',', '.') }}</td><td class="p-3 text-right">Rp {{ number_format($product->gross_profit, 0, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="p-6 text-center text-neutral-500">Belum ada penjualan lunas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white">
            <div class="border-b border-neutral-200 bg-neutral-50 p-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-700">Stok Menipis / Kritis</h2>
            </div>
            <div class="divide-y divide-neutral-100">
                @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between gap-3 p-3 text-xs">
                        <span class="font-semibold text-neutral-900">{{ $product->name }}</span>
                        <span class="shrink-0 font-mono text-orange-700">{{ $product->stock }} / min {{ $product->min_stock }} {{ $product->unit }}</span>
                    </div>
                @empty
                    <p class="p-6 text-center text-xs text-neutral-500">Tidak ada stok kritis.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white">
        <div class="border-b border-neutral-200 bg-neutral-50 p-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-700">Transaksi Lunas</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-neutral-500"><tr><th class="p-3">Waktu</th><th class="p-3">Nomor</th><th class="p-3">Kanal</th><th class="p-3">Pelanggan</th><th class="p-3 text-right">Omzet</th></tr></thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($orders as $order)
                        <tr><td class="whitespace-nowrap p-3">{{ $order->paid_at?->format('d/m/Y H:i') }}</td><td class="p-3 font-mono font-bold">{{ $order->order_number }}</td><td class="p-3">{{ strtoupper($order->type) }}</td><td class="p-3">{{ $order->customer->name ?? 'Pelanggan Kasir' }}</td><td class="p-3 text-right font-mono">Rp {{ number_format($order->total, 0, ',', '.') }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-neutral-500">Tidak ada transaksi lunas pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-neutral-100 p-4">{{ $orders->links() }}</div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white">
        <div class="border-b border-neutral-200 bg-neutral-50 p-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-700">Mutasi Stok Terbaru dalam Periode</h2>
        </div>
        <div class="divide-y divide-neutral-100">
            @forelse($movements as $movement)
                <div class="flex flex-wrap items-center justify-between gap-2 p-3 text-xs">
                    <span>{{ $movement->created_at->format('d/m/Y H:i') }} · {{ $movement->product->name ?? 'Produk dihapus' }}</span>
                    <span class="text-neutral-600">{{ $movement->reason }} · {{ $movement->stock_before }} → {{ $movement->stock_after }}</span>
                </div>
            @empty
                <p class="p-6 text-center text-xs text-neutral-500">Tidak ada mutasi stok pada periode ini.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection

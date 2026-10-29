@extends('layouts.owner', ['title' => 'Dashboard Analitik - Mobeli', 'header' => 'Ringkasan Kinerja Toko & Analitik'])

@section('content')
<div class="space-y-6">
    <!-- Top Welcome Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-800 to-emerald-900 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-300">Sistem Pendukung Keputusan Toko Kelontong</span>
            <h1 class="text-2xl font-extrabold font-heading mt-1">Dashboard Pemilik Toko</h1>
            <p class="text-xs text-emerald-200 mt-1 max-w-lg">
                Pantau omzet penjualan offline & online secara terintegrasi serta lihat saran restock otomatis berbasis metode TOPSIS.
            </p>
        </div>
        <form action="{{ route('admin.topsis.calculate.run') }}" method="POST" class="flex items-center gap-2">
            @csrf
            <button type="submit" class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-extrabold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i data-lucide="calculator" class="w-4 h-4"></i> Hitung Ulang TOPSIS
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-xs">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold font-heading text-neutral-900">Omzet 7 Hari Terakhir</h2>
                    <p class="text-[11px] text-neutral-500">Hanya transaksi lunas, berdasarkan waktu pembayaran.</p>
                </div>
                <i data-lucide="chart-no-axes-column-increasing" class="h-5 w-5 text-primary"></i>
            </div>
            @php $maxDailyRevenue = max(1, (float) $salesChart->max('revenue')); @endphp
            <div class="space-y-3">
                @foreach($salesChart as $day)
                    <div class="flex items-center gap-3 text-[11px]">
                        <span class="w-8 shrink-0 font-semibold text-neutral-500">{{ $day['label'] }}</span>
                        <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-blue-50">
                            <div class="h-full rounded-full bg-primary" style="width: {{ min(100, ($day['revenue'] / $maxDailyRevenue) * 100) }}%"></div>
                        </div>
                        <span class="w-28 shrink-0 text-right font-mono text-neutral-600">Rp {{ number_format($day['revenue'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs">
            <div class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50 p-4">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-700">Stok Kritis</h2>
                    <p class="mt-1 text-[11px] text-neutral-500">{{ $lowStockProducts->count() }} produk menyentuh atau di bawah stok minimum</p>
                </div>
                <a href="{{ route('admin.stock') }}" class="text-xs font-bold text-primary hover:underline">Kelola stok</a>
            </div>
            <div class="divide-y divide-neutral-100">
                @forelse($lowStockProducts->take(5) as $product)
                    <div class="flex items-center justify-between gap-3 p-3 text-xs">
                        <span class="font-semibold text-neutral-900">{{ $product->name }}</span>
                        <span class="shrink-0 font-mono text-orange-700">{{ $product->stock }} / min {{ $product->min_stock }} {{ $product->unit }}</span>
                    </div>
                @empty
                    <p class="p-5 text-center text-xs text-neutral-500">Tidak ada stok kritis.</p>
                @endforelse
            </div>
        </section>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-neutral-200 shadow-xs">
            <span class="text-xs text-neutral-400 font-bold uppercase tracking-wider block">Penjualan Hari Ini (Omzet)</span>
            <div class="text-2xl font-black text-neutral-900 price-font mt-2">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-emerald-600 font-bold mt-1 inline-block">Transaksi lunas hari ini</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-neutral-200 shadow-xs">
            <span class="text-xs text-neutral-400 font-bold uppercase tracking-wider block">Transaksi Lunas Hari Ini</span>
            <div class="text-2xl font-black text-neutral-900 price-font mt-2">
                {{ $totalOrders }} Transaksi
            </div>
            <span class="text-[11px] text-neutral-500 mt-1 inline-block">Online & offline POS</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-neutral-200 shadow-xs">
            <span class="text-xs text-neutral-400 font-bold uppercase tracking-wider block">Total SKU Produk Sembako</span>
            <div class="text-2xl font-black text-neutral-900 price-font mt-2">
                {{ $totalProducts }} Item
            </div>
            <span class="text-[11px] text-emerald-600 font-bold mt-1 inline-block">Tersedia di Toko</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-neutral-200 shadow-xs">
            <span class="text-xs text-neutral-400 font-bold uppercase tracking-wider block">Total Unit Stok</span>
            <div class="text-2xl font-black text-neutral-900 price-font mt-2">
                {{ number_format($totalStockUnits, 0, ',', '.') }} Unit
            </div>
            <span class="text-[11px] text-neutral-500 mt-1 inline-block">Dari seluruh produk aktif</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-neutral-200 shadow-xs">
            <span class="text-xs text-neutral-400 font-bold uppercase tracking-wider block">Stok Menipis / Kritis</span>
            <div class="text-2xl font-black text-orange-600 price-font mt-2">
                {{ $lowStockProducts->count() }} Produk
            </div>
            <span class="text-[11px] text-orange-600 font-bold mt-1 inline-block">Perlu di-restock</span>
        </div>
    </div>

    <!-- TOPSIS Insights Highlight -->
    <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-neutral-100">
            <div>
                <h3 class="text-base font-bold font-heading text-neutral-900">Rekomendasi Terkini Algoritma TOPSIS</h3>
                <p class="text-xs text-neutral-500">Berdasarkan empat kriteria TOPSIS: urgensi stok, kecepatan penjualan, waktu pengadaan, dan margin.</p>
            </div>
            <a href="{{ route('admin.topsis.recommendations') }}" class="text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1">
                Laporan Lengkap & Ranking <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        @if($latestTopsis && !empty($latestTopsis->preferences))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach(array_slice($latestTopsis->preferences, 0, 3) as $position => $pref)
                    <div class="p-3.5 rounded-xl border border-neutral-200 bg-neutral-50/70 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] font-bold font-mono text-slate-500">Preferensi: {{ number_format((float) $pref['ci'], 6, ',', '.') }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold {{ $position === 0 ? 'bg-blue-100 text-blue-800' : 'bg-neutral-100 text-neutral-700' }}">
                                    Prioritas #{{ $position + 1 }}
                                </span>
                            </div>
                            <h4 class="text-xs font-bold text-neutral-900 line-clamp-1">{{ $pref['product']['name'] }}</h4>
                            <p class="text-[11px] text-neutral-500 mt-1">Stok saat ini: {{ $pref['product']['stock'] }} {{ $pref['product']['unit'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center text-xs text-neutral-500 bg-neutral-50 rounded-xl">
                Belum ada kalkulasi TOPSIS tersimpan. <a href="{{ route('admin.topsis.criteria') }}" class="font-bold text-blue-700 underline">Lengkapi bobot dan data kriteria</a> sebelum menjalankan perhitungan.
            </div>
        @endif
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden shadow-xs">
        <div class="p-4 bg-neutral-50 border-b border-neutral-200 flex items-center justify-between">
            <h3 class="font-bold text-xs uppercase tracking-wider text-neutral-700">Pesanan Masuk Terbaru</h3>
            <a href="{{ route('admin.orders') }}" class="text-xs text-emerald-600 font-semibold hover:underline">
                Kelola Semua Pesanan
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-100/70 border-b border-neutral-200 text-neutral-600 uppercase font-semibold">
                    <tr>
                        <th class="p-3">No. Pesanan</th>
                        <th class="p-3">Kanal</th>
                        <th class="p-3">Pelanggan</th>
                        <th class="p-3 text-right">Total</th>
                        <th class="p-3 text-center">Metode</th>
                        <th class="p-3 text-center">Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($recentOrders as $ro)
                        <tr class="hover:bg-neutral-50">
                            <td class="p-3 font-mono font-bold text-neutral-900">
                                <a href="{{ route('admin.orders') }}" class="hover:text-primary">
                                    {{ $ro->order_number }}
                                </a>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $ro->type === 'online' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800' }}">
                                    {{ strtoupper($ro->type) }}
                                </span>
                            </td>
                            <td class="p-3 font-medium">{{ $ro->customer->name ?? 'Pelanggan Kasir' }}</td>
                            <td class="p-3 text-right font-bold text-emerald-700 price-font">
                                Rp {{ number_format($ro->total, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded bg-neutral-100 text-[10px] font-mono font-bold">
                                    {{ $ro->payment_method }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ro->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                                    {{ strtoupper($ro->payment_status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-neutral-400">Belum ada pesanan masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

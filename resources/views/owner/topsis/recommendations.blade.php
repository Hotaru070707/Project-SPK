@extends('layouts.owner', ['title' => 'Rekomendasi TOPSIS - Mobeli', 'header' => 'Rekomendasi Strategis Toko (Hasil Analisis TOPSIS)'])

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Summary -->
    <div class="bg-white p-6 rounded-2xl border border-neutral-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Hasil Keputusan Cerdas SPK</span>
            <h2 class="text-xl font-bold font-heading text-neutral-900 mt-0.5">Insight & Rekomendasi Tindakan Toko</h2>
            <p class="text-xs text-neutral-500 mt-1">
                Data dihitung otomatis berdasarkan preferensi kedekatan relatif ($C_i$) terhadap solusi ideal positif dan negatif.
                Perhitungan terakhir: {{ $latestResult->created_at->format('d/m/Y H:i') }}.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="px-3.5 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                <i data-lucide="printer" class="w-4 h-4"></i> Cetak Laporan
            </button>
            <a href="{{ route('admin.topsis.calculate') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Hitung Ulang Data Baru
            </a>
        </div>
    </div>

    <!-- 3 Actionable Groups Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 1. Restock Segera -->
        <div class="bg-white rounded-2xl border-2 border-orange-400 p-5 shadow-xs space-y-3">
            <div class="flex items-center justify-between pb-3 border-b border-orange-100">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-700 flex items-center justify-center font-bold text-sm">
                        ⚠️
                    </span>
                    <div>
                        <h3 class="font-bold text-sm text-neutral-900">Restock Segera</h3>
                        <span class="text-[10px] text-orange-600 font-medium">Stok < Ambang Batas Minimum</span>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                @php
                    $restockList = collect($preferences)->filter(function($i) {
                        return $i['product']['stock'] <= $i['product']['min_stock'];
                    });
                @endphp

                @forelse($restockList as $item)
                    <div class="p-3 bg-orange-50/60 rounded-xl border border-orange-100">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-neutral-900">{{ $item['product']['name'] }}</span>
                            <span class="text-[10px] font-mono text-orange-700 font-bold">Ci: {{ $item['ci'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-neutral-500 mt-1">
                            <span>Sisa: <strong class="text-orange-700 font-bold">{{ $item['product']['stock'] }}</strong> (Min: {{ $item['product']['min_stock'] }})</span>
                            <span class="text-emerald-700 font-semibold">Urgensi Tinggi</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-neutral-400 py-4 text-center">Semua stok produk saat ini masih aman.</p>
                @endforelse
            </div>
        </div>

        <!-- 2. Produk Unggulan -->
        <div class="bg-white rounded-2xl border-2 border-emerald-500 p-5 shadow-xs space-y-3">
            <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                        ⭐
                    </span>
                    <div>
                        <h3 class="font-bold text-sm text-neutral-900">Produk Unggulan</h3>
                        <span class="text-[10px] text-emerald-600 font-medium">Performa Tertinggi & Margin Bagus</span>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                @php
                    $topList = collect($preferences)->take(3);
                @endphp

                @foreach($topList as $item)
                    <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-100">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-neutral-900">{{ $item['product']['name'] }}</span>
                            <span class="text-[10px] font-mono text-emerald-800 font-bold">Ci: {{ $item['ci'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-neutral-500 mt-1">
                            <span>Harga: Rp {{ number_format($item['product']['sell_price'], 0, ',', '.') }}</span>
                            <span class="text-emerald-800 font-bold">Peringkat Teratas</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 3. Rekomendasi Promo / Clearance -->
        <div class="bg-white rounded-2xl border-2 border-blue-400 p-5 shadow-xs space-y-3">
            <div class="flex items-center justify-between pb-3 border-b border-blue-100">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                        🏷️
                    </span>
                    <div>
                        <h3 class="font-bold text-sm text-neutral-900">Promo Clearance</h3>
                        <span class="text-[10px] text-blue-600 font-medium">Percepat Perputaran Uang Kas</span>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                @php
                    $clearanceList = collect($preferences)->filter(function($i) {
                        return $i['product']['stock'] > 30;
                    })->take(3);
                @endphp

                @forelse($clearanceList as $item)
                    <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-100">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-neutral-900">{{ $item['product']['name'] }}</span>
                            <span class="text-[10px] font-mono text-blue-800 font-bold">Stok: {{ $item['product']['stock'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-neutral-500 mt-1">
                            <span>Disarankan diskon 5-10%</span>
                            <span class="text-blue-700 font-semibold">Bundling Sembako</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-neutral-400 py-4 text-center">Tidak ada penumpukan stok berlebih.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Complete Ranking Table -->
    <div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden shadow-xs">
        <div class="p-4 bg-neutral-50 border-b border-neutral-200 flex items-center justify-between">
            <h3 class="font-bold text-xs uppercase tracking-wider text-neutral-700">Tabel Perankingan Seluruh Alternatif Produk</h3>
            <span class="text-xs text-neutral-500">Total: {{ count($preferences) }} Produk Dievaluasi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-100/70 border-b border-neutral-200 text-neutral-600 uppercase font-semibold">
                    <tr>
                        <th class="p-3 text-center w-12">Peringkat</th>
                        <th class="p-3">Nama Produk Sembako</th>
                        <th class="p-3 text-right">Harga Jual</th>
                        <th class="p-3 text-center">Sisa Stok</th>
                        <th class="p-3 text-center font-mono">Skor Preferensi ($C_i$)</th>
                        <th class="p-3 text-center">Status Rekomendasi</th>
                        <th class="p-3">Alasan Prioritas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @php $no = 1; @endphp
                    @foreach($preferences as $item)
                        <tr class="hover:bg-neutral-50 {{ $no <= 3 ? 'bg-emerald-50/20' : '' }}">
                            <td class="p-3 text-center">
                                <span class="w-6 h-6 rounded-full inline-flex items-center justify-center font-bold {{ $no === 1 ? 'bg-amber-400 text-slate-900' : ($no <= 3 ? 'bg-emerald-600 text-white' : 'bg-neutral-200 text-neutral-700') }}">
                                    {{ $no }}
                                </span>
                            </td>
                            <td class="p-3 font-bold text-neutral-900">{{ $item['product']['name'] }}</td>
                            <td class="p-3 text-right price-font">Rp {{ number_format($item['product']['sell_price'], 0, ',', '.') }}</td>
                            <td class="p-3 text-center font-mono {{ $item['product']['stock'] <= $item['product']['min_stock'] ? 'text-red-600 font-bold' : '' }}">
                                {{ $item['product']['stock'] }} {{ $item['product']['unit'] }}
                            </td>
                            <td class="p-3 text-center font-mono font-extrabold text-sm text-emerald-800">
                                {{ number_format($item['ci'], 4) }}
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $item['product']['stock'] <= $item['product']['min_stock'] ? 'bg-orange-100 text-orange-800' : ($no <= 3 ? 'bg-emerald-100 text-emerald-800' : 'bg-neutral-100 text-neutral-700') }}">
                                    {{ $item['product']['stock'] <= $item['product']['min_stock'] ? 'Restock Segera' : ($no <= 3 ? 'Produk Unggulan' : 'Normal') }}
                                </span>
                            </td>
                            <td class="p-3 text-neutral-600">{{ $item['reason'] ?? ($item['recommendation'] ?? 'Evaluasi berbasis skor preferensi TOPSIS.') }}</td>
                        </tr>
                        @php $no++; @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

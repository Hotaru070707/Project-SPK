@extends('layouts.admin', ['title' => 'Perhitungan TOPSIS - Mobeli', 'header' => 'Langkah Perhitungan Lengkap Algoritma TOPSIS'])

@section('content')
<div class="space-y-8 pb-12">
    <!-- Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-neutral-200 shadow-xs">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Audit Trail SPK</span>
            <h2 class="text-lg font-bold font-heading text-neutral-900">Perhitungan Matriks TOPSIS 6 Langkah</h2>
            <p class="text-xs text-neutral-500">Transparansi matematis dari data mentah toko hingga menghasilkan nilai preferensi.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="px-3.5 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak Dokumen
            </button>
            <a href="{{ route('admin.topsis.recommendations') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="award" class="w-3.5 h-3.5"></i> Lihat Hasil Akhir Rekomendasi
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-neutral-200 p-5 shadow-xs">
        <h3 class="text-sm font-bold font-heading text-neutral-900 mb-3">Kriteria dan Bobot Perhitungan</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
            @foreach($criteria as $criterion)
                <div class="rounded-xl border border-neutral-100 bg-neutral-50 p-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-primary">{{ $criterion->code }}</span>
                        <span class="font-mono text-xs font-bold text-neutral-700">{{ number_format((float) $criterion->weight * 100, 0) }}%</span>
                    </div>
                    <p class="mt-1 text-xs font-bold text-neutral-900">{{ $criterion->name }}</p>
                    <p class="mt-1 text-[11px] text-neutral-500">{{ $criterion->description }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-[11px] text-neutral-500">Kecepatan penjualan menggunakan transaksi lunas 30 hari terakhir. Perhitungan dihentikan bila waktu pengadaan belum diisi pada salah satu produk aktif.</p>
    </div>

    <!-- LANGKAH 1: Matriks Keputusan (X) -->
    <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-xs space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-neutral-100">
            <h3 class="text-sm font-bold font-heading text-neutral-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-mono font-bold">1</span>
                Matriks Keputusan (X)
            </h3>
            <span class="text-[11px] text-neutral-400 font-mono">Nilai Asli Metrik Toko</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 border-b border-neutral-200 text-neutral-600 uppercase font-semibold">
                    <tr>
                        <th class="p-2.5">Produk (Alternatif)</th>
                        @foreach($criteria as $c)
                            <th class="p-2.5 text-center font-mono">{{ $c->code }} ({{ $c->type }})</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach($matrixX as $row)
                        <tr class="hover:bg-neutral-50">
                            <td class="p-2.5 font-bold text-neutral-900 truncate max-w-xs">{{ $row['product']->name }}</td>
                            @foreach($criteria as $c)
                                <td class="p-2.5 text-center font-mono">{{ number_format((float) $row[$c->code], 4) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- LANGKAH 2: Matriks Normalisasi (R) -->
    <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-xs space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-neutral-100">
            <h3 class="text-sm font-bold font-heading text-neutral-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-mono font-bold">2</span>
                Matriks Ternormalisasi (R)
            </h3>
            <span class="text-[11px] text-neutral-400 font-mono">r_ij = x_ij / √(Σ x_kj²)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 border-b border-neutral-200 text-neutral-600 uppercase font-semibold">
                    <tr>
                        <th class="p-2.5">Produk (Alternatif)</th>
                        @foreach($criteria as $c)
                            <th class="p-2.5 text-center font-mono">{{ $c->code }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach($matrixR as $row)
                        <tr class="hover:bg-neutral-50">
                            <td class="p-2.5 font-bold text-neutral-900 truncate max-w-xs">{{ $row['product']->name }}</td>
                            @foreach($criteria as $c)
                                <td class="p-2.5 text-center font-mono text-emerald-800 font-semibold">{{ number_format((float) $row[$c->code], 4) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- LANGKAH 3: Matriks Terbobot (Y) -->
    <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-xs space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-neutral-100">
            <h3 class="text-sm font-bold font-heading text-neutral-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-mono font-bold">3</span>
                Matriks Ternormalisasi Terbobot (Y)
            </h3>
            <span class="text-[11px] text-neutral-400 font-mono">y_ij = w_j × r_ij</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 border-b border-neutral-200 text-neutral-600 uppercase font-semibold">
                    <tr>
                        <th class="p-2.5">Produk (Alternatif)</th>
                        @foreach($criteria as $c)
                            <th class="p-2.5 text-center font-mono">{{ $c->code }} (w={{ $c->weight }})</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach($matrixY as $row)
                        <tr class="hover:bg-neutral-50">
                            <td class="p-2.5 font-bold text-neutral-900 truncate max-w-xs">{{ $row['product']->name }}</td>
                            @foreach($criteria as $c)
                                <td class="p-2.5 text-center font-mono text-slate-800">{{ number_format((float) $row[$c->code], 4) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- LANGKAH 4: Solusi Ideal Positif (A+) & Negatif (A-) -->
    <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-xs space-y-3">
        <div class="pb-2 border-b border-neutral-100">
            <h3 class="text-sm font-bold font-heading text-neutral-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-mono font-bold">4</span>
                Solusi Ideal Positif ($A^+$) dan Solusi Ideal Negatif ($A^-$)
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200 text-xs">
                <span class="font-extrabold text-emerald-900 block mb-2">Solusi Ideal Positif ($A^+$):</span>
                <div class="grid grid-cols-3 gap-2 font-mono">
                    @foreach($idealPositive as $code => $val)
                        <div class="p-2 bg-white rounded border border-emerald-200">
                            <span class="text-neutral-400 block text-[10px]">{{ $code }}</span>
                            <span class="font-bold text-emerald-800">{{ number_format((float) $val, 4) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="p-4 rounded-xl bg-orange-50/70 border border-orange-200 text-xs">
                <span class="font-extrabold text-orange-900 block mb-2">Solusi Ideal Negatif ($A^-$):</span>
                <div class="grid grid-cols-3 gap-2 font-mono">
                    @foreach($idealNegative as $code => $val)
                        <div class="p-2 bg-white rounded border border-orange-200">
                            <span class="text-neutral-400 block text-[10px]">{{ $code }}</span>
                            <span class="font-bold text-orange-800">{{ number_format((float) $val, 4) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- LANGKAH 5 & 6: Jarak (D+, D-) dan Nilai Preferensi (Ci) -->
    <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-neutral-100">
            <h3 class="text-sm font-bold font-heading text-neutral-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-mono font-bold">5 & 6</span>
                Jarak Euclidean ($D^+, D^-$), Nilai Preferensi ($C_i$), dan Ranking
            </h3>
            <span class="text-[11px] text-neutral-400 font-mono">C_i = D^- / (D^+ + D^-)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 border-b border-neutral-200 text-neutral-600 uppercase font-semibold">
                    <tr>
                        <th class="p-3 text-center w-12">Rank</th>
                        <th class="p-3">Nama Produk</th>
                        <th class="p-3 text-center font-mono">Jarak D+</th>
                        <th class="p-3 text-center font-mono">Jarak D-</th>
                        <th class="p-3 text-center font-mono">Preferensi (Ci)</th>
                        <th class="p-3 text-center">Rekomendasi Actionable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @php $rank = 1; @endphp
                    @foreach($preferences as $pid => $item)
                        <tr class="hover:bg-neutral-50 {{ $rank <= 3 ? 'bg-emerald-50/30' : '' }}">
                            <td class="p-3 text-center font-black">
                                <span class="w-6 h-6 rounded-full inline-flex items-center justify-center {{ $rank === 1 ? 'bg-amber-400 text-slate-900 font-black' : ($rank <= 3 ? 'bg-emerald-600 text-white' : 'bg-neutral-200 text-neutral-700') }}">
                                    {{ $rank }}
                                </span>
                            </td>
                            <td class="p-3 font-bold text-neutral-900">{{ $item['product']->name }}</td>
                            <td class="p-3 text-center font-mono">{{ number_format($distances[$pid]['d_plus'], 4) }}</td>
                            <td class="p-3 text-center font-mono">{{ number_format($distances[$pid]['d_minus'], 4) }}</td>
                            <td class="p-3 text-center font-mono font-extrabold text-emerald-800 text-sm">
                                {{ number_format($item['ci'], 4) }}
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $item['recommendation'] === 'Restock Segera' ? 'bg-orange-100 text-orange-800' : ($item['recommendation'] === 'Produk Unggulan' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ $item['recommendation'] }}
                                </span>
                            </td>
                        </tr>
                        @php $rank++; @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

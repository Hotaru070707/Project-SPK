@extends('layouts.owner', ['title' => 'Rekomendasi TOPSIS - Mobeli', 'header' => 'Rekomendasi Prioritas Restock'])

@section('content')
<div class="space-y-6 pb-10">
    <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-xs sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Hasil analisis TOPSIS</span>
                <h1 class="mt-1 font-heading text-xl font-extrabold text-neutral-900">Prioritas Relatif Restock Produk</h1>
                <p class="mt-2 text-sm text-neutral-600">
                    Perhitungan {{ $latestResult->created_at->format('d/m/Y H:i') }} · Periode penjualan
                    {{ \Illuminate\Support\Carbon::parse($latestResult->period_start)->format('d/m/Y H:i') }}–{{ \Illuminate\Support\Carbon::parse($latestResult->period_end)->format('d/m/Y H:i') }}.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.topsis.calculate') }}" class="inline-flex items-center gap-2 rounded-xl border border-neutral-200 px-4 py-2.5 text-xs font-bold text-neutral-700 hover:bg-neutral-50">
                    <i data-lucide="table-2" class="h-4 w-4"></i> Detail perhitungan
                </a>
                <a href="{{ route('admin.topsis.history') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-800">
                    <i data-lucide="history" class="h-4 w-4"></i> Riwayat
                </a>
            </div>
        </div>
        @if ($isLegacyResult)
            <div role="status" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-5 text-amber-900">
                Hasil ini berasal dari skema kriteria terdahulu. Data ditampilkan sesuai snapshot saat perhitungan dan tidak dihitung ulang dengan kriteria terbaru.
            </div>
        @endif
    </section>

    <section class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm leading-6 text-blue-950">
        Ranking menunjukkan prioritas relatif antarproduk berdasarkan data yang dinilai. Ranking bukan kepastian stok akan habis dan tidak menentukan jumlah unit yang perlu dibeli.
    </section>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs">
        <div class="flex flex-col gap-1 border-b border-neutral-100 p-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="font-heading text-sm font-bold text-neutral-900">Ranking seluruh alternatif produk</h2>
                <p class="mt-1 text-xs text-neutral-500">Nilai kriteria dan stok ditampilkan dari snapshot saat perhitungan.</p>
            </div>
            <span class="text-xs font-semibold text-neutral-500">{{ count($preferences) }} produk dievaluasi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-left text-xs">
                <thead class="bg-neutral-50 text-[10px] uppercase tracking-wide text-neutral-500">
                    <tr>
                        <th class="p-3">Ranking</th>
                        <th class="p-3">Nama produk</th>
                        <th class="p-3 text-right">Jumlah terjual</th>
                        <th class="p-3 text-right">Stok tersedia</th>
                        <th class="p-3 text-right">Waktu pengadaan</th>
                        <th class="p-3 text-right">Preferensi TOPSIS</th>
                        <th class="p-3">Status rekomendasi</th>
                        <th class="p-3">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach ($preferences as $position => $item)
                        @php
                            $rank = (int) ($item['rank'] ?? ($position + 1));
                            $salesCode = $isLegacyResult ? 'C2' : 'C1';
                            $metrics = $item['metrics'] ?? [];
                            $unitsSold = $metrics[$salesCode] ?? null;
                            $product = $item['product'];
                        @endphp
                        <tr class="{{ $rank === 1 ? 'bg-blue-50/50' : '' }}">
                            <td class="p-3">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full font-bold {{ $rank === 1 ? 'bg-blue-700 text-white' : 'bg-neutral-100 text-neutral-700' }}">
                                    {{ $rank }}
                                </span>
                            </td>
                            <td class="p-3 font-bold text-neutral-900">{{ $product['name'] }}</td>
                            <td class="p-3 text-right font-mono">{{ $unitsSold === null ? '—' : number_format((float) $unitsSold, 0, ',', '.') }} {{ $unitsSold === null ? '' : 'unit' }}</td>
                            <td class="p-3 text-right font-mono">{{ isset($product['stock']) ? number_format((int) $product['stock'], 0, ',', '.') : '—' }} {{ $product['unit'] ?? '' }}</td>
                            <td class="p-3 text-right font-mono">{{ isset($product['supplier_lead_time_days']) ? number_format((int) $product['supplier_lead_time_days'], 0, ',', '.').' hari' : '—' }}</td>
                            <td class="p-3 text-right font-mono font-bold text-blue-800">{{ number_format((float) $item['ci'], 6, ',', '.') }}</td>
                            <td class="p-3">
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $rank === 1 ? 'bg-blue-100 text-blue-800' : 'bg-neutral-100 text-neutral-700' }}">
                                    {{ $rank === 1 ? 'Prioritas tertinggi' : 'Prioritas relatif' }}
                                </span>
                            </td>
                            <td class="p-3">
                                <a href="{{ route('admin.topsis.history.show', $latestResult) }}" class="inline-flex items-center gap-1.5 font-bold text-blue-700 hover:text-blue-900">
                                    <i data-lucide="external-link" class="h-3.5 w-3.5"></i> Lihat hitungan
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="border-t border-neutral-100 px-5 py-4 text-xs leading-5 text-neutral-500">
            Jumlah terjual berasal dari unit pada detail transaksi lunas selama periode penilaian. Stok dan waktu pengadaan mencerminkan kondisi ketika perhitungan disimpan.
        </p>
    </section>
</div>
@endsection

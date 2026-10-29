@extends('layouts.owner', ['title' => 'Riwayat TOPSIS - Mobeli', 'header' => 'Riwayat Perhitungan TOPSIS'])

@section('content')
<div class="space-y-6 pb-10">
    <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-xs sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Snapshot audit</span>
                <h1 class="mt-1 font-heading text-xl font-extrabold text-neutral-900">Riwayat Perhitungan</h1>
                <p class="mt-2 text-sm text-neutral-600">Setiap hasil tersimpan bersama periode, bobot, data alternatif, dan matriks yang digunakan saat itu.</p>
            </div>
            <a href="{{ route('admin.topsis.criteria') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-neutral-200 px-4 py-2.5 text-xs font-bold text-neutral-700 hover:bg-neutral-50">
                <i data-lucide="sliders-horizontal" class="h-4 w-4"></i> Pengaturan kriteria
            </a>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-xs">
                <thead class="bg-neutral-50 text-[10px] uppercase tracking-wide text-neutral-500">
                    <tr>
                        <th class="p-4">Waktu perhitungan</th>
                        <th class="p-4">Periode penjualan</th>
                        <th class="p-4">Dijalankan oleh</th>
                        <th class="p-4">Alternatif</th>
                        <th class="p-4 text-right">Bobot</th>
                        <th class="p-4">Hasil snapshot</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse ($results as $result)
                        @php
                            $snapshot = $result->criteria_snapshot;
                            $criteria = isset($snapshot['criteria']) ? $snapshot['criteria'] : array_values($snapshot);
                            $weightTotal = collect($criteria)->sum(fn ($criterion) => (float) ($criterion['weight'] ?? 0));
                        @endphp
                        <tr class="hover:bg-neutral-50/70">
                            <td class="p-4 font-semibold text-neutral-900">{{ $result->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-4 text-neutral-600">
                                {{ \Illuminate\Support\Carbon::parse($result->period_start)->format('d/m/Y H:i') }}
                                <span class="px-1 text-neutral-400">–</span>
                                {{ \Illuminate\Support\Carbon::parse($result->period_end)->format('d/m/Y H:i') }}
                            </td>
                            <td class="p-4 text-neutral-600">{{ $result->generator?->name ?? 'Pengguna tidak tersedia' }}</td>
                            <td class="p-4 text-neutral-600">{{ count($result->decision_matrix) }} produk</td>
                            <td class="p-4 text-right font-mono font-semibold text-neutral-800">{{ number_format($weightTotal * 100, 2, ',', '.') }}%</td>
                            <td class="p-4">
                                <a href="{{ route('admin.topsis.history.show', $result) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-2 font-bold text-blue-800 hover:bg-blue-100">
                                    <i data-lucide="file-search" class="h-3.5 w-3.5"></i> Buka detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-neutral-100 text-neutral-500">
                                    <i data-lucide="history" class="h-6 w-6"></i>
                                </div>
                                <p class="mt-3 font-bold text-neutral-800">Belum ada riwayat perhitungan</p>
                                <p class="mt-1 text-xs text-neutral-500">Jalankan perhitungan TOPSIS untuk menyimpan snapshot pertama.</p>
                                <a href="{{ route('admin.topsis.criteria') }}" class="mt-4 inline-flex rounded-xl bg-blue-700 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-800">Ke pengaturan TOPSIS</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($results->hasPages())
            <div class="border-t border-neutral-100 px-5 py-4">{{ $results->links() }}</div>
        @endif
    </section>
</div>
@endsection

@extends('layouts.owner', ['title' => 'Matriks TOPSIS - Mobeli', 'header' => 'Matriks & Perhitungan TOPSIS'])

@section('content')
<div class="space-y-6 pb-10">
    <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-xs sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Audit perhitungan SPK</span>
                <h1 class="mt-1 font-heading text-xl font-extrabold text-neutral-900">Tahapan Perhitungan TOPSIS</h1>
                <p class="mt-2 text-sm text-neutral-600">
                    Snapshot hasil {{ $result->created_at->format('d/m/Y H:i') }}
                    @if ($result->generator)
                        oleh {{ $result->generator->name }}
                    @endif
                    · Periode penjualan                     {{ \Illuminate\Support\Carbon::parse($result->period_start)->format('d/m/Y H:i') }}–{{ \Illuminate\Support\Carbon::parse($result->period_end)->format('d/m/Y H:i') }}.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-neutral-200 px-4 py-2.5 text-xs font-bold text-neutral-700 hover:bg-neutral-50">
                    <i data-lucide="printer" class="h-4 w-4"></i> Cetak
                </button>
                <a href="{{ route('admin.topsis.recommendations') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-800">
                    <i data-lucide="award" class="h-4 w-4"></i> Rekomendasi
                </a>
                <a href="{{ route('admin.topsis.history') }}" class="inline-flex items-center gap-2 rounded-xl border border-neutral-200 px-4 py-2.5 text-xs font-bold text-neutral-700 hover:bg-neutral-50">
                    <i data-lucide="history" class="h-4 w-4"></i> Riwayat
                </a>
            </div>
        </div>
        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
            @foreach ($criteria as $criterion)
                <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-3.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-blue-700">{{ $criterion['code'] }}</span>
                        <span class="rounded-full px-2 py-1 text-[10px] font-bold uppercase {{ ($criterion['type'] ?? '') === 'benefit' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ ($criterion['type'] ?? '') === 'benefit' ? 'Benefit' : 'Cost' }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs font-bold text-neutral-900">{{ $criterion['name'] }}</p>
                    <p class="mt-1 text-[11px] text-neutral-500">Bobot {{ number_format((float) $criterion['weight'] * 100, 2, ',', '.') }}%</p>
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-neutral-500">Data penjualan dihitung dari transaksi berstatus lunas dan item normal selama periode di atas.</p>
    </section>

    @if ($zeroDivisorCodes !== [])
        <div role="status" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Penyebut normalisasi bernilai nol untuk {{ implode(', ', $zeroDivisorCodes) }}. Nilai normalisasinya ditetapkan 0 agar tidak menghasilkan NaN atau Infinity.
        </div>
    @endif
    @if ($alternativesIdentical)
        <div role="status" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
            Semua alternatif memiliki nilai kriteria identik. TOPSIS tidak dapat membedakan prioritas berdasarkan data ini; nilai preferensi yang sama ditampilkan sebagai 0,500000.
        </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs">
        <div class="border-b border-neutral-100 p-5">
            <h2 class="font-heading text-sm font-bold text-neutral-900">1. Matriks keputusan awal (X)</h2>
            <p class="mt-1 text-xs text-neutral-500">Nilai aktual yang menjadi dasar analisis: unit terjual, stok tersedia, dan waktu pengadaan dalam hari.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-xs">
                <thead class="bg-neutral-50 text-neutral-600">
                    <tr>
                        <th class="p-3">Alternatif produk</th>
                        @foreach ($criteria as $criterion)
                            <th class="p-3 text-right">{{ $criterion['code'] }} · {{ $criterion['name'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach ($matrixX as $row)
                        <tr>
                            <td class="p-3 font-bold text-neutral-900">{{ $row['product']['name'] }}</td>
                            @foreach ($criteria as $criterion)
                                <td class="p-3 text-right font-mono">{{ number_format((float) $row[$criterion['code']], 4, ',', '.') }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs">
        <div class="border-b border-neutral-100 p-5">
            <h2 class="font-heading text-sm font-bold text-neutral-900">2. Matriks normalisasi (R)</h2>
            <p class="mt-1 text-xs text-neutral-500">Setiap nilai dibagi akar jumlah kuadrat seluruh alternatif pada kolom kriteria yang sama.</p>
            <p class="mt-2 font-mono text-[11px] text-blue-800">rᵢⱼ = xᵢⱼ / √(Σ xₖⱼ²)</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-xs">
                <thead class="bg-neutral-50 text-neutral-600">
                    <tr>
                        <th class="p-3">Alternatif produk</th>
                        @foreach ($criteria as $criterion)
                            <th class="p-3 text-right">{{ $criterion['code'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach ($matrixR as $row)
                        <tr>
                            <td class="p-3 font-bold text-neutral-900">{{ $row['product']['name'] }}</td>
                            @foreach ($criteria as $criterion)
                                <td class="p-3 text-right font-mono">{{ number_format((float) $row[$criterion['code']], 6, ',', '.') }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs">
        <div class="border-b border-neutral-100 p-5">
            <h2 class="font-heading text-sm font-bold text-neutral-900">3. Matriks normalisasi terbobot (Y)</h2>
            <p class="mt-1 text-xs text-neutral-500">Nilai normalisasi dikalikan bobot masing-masing kriteria tanpa pembulatan antara.</p>
            <p class="mt-2 font-mono text-[11px] text-blue-800">yᵢⱼ = wⱼ × rᵢⱼ</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-xs">
                <thead class="bg-neutral-50 text-neutral-600">
                    <tr>
                        <th class="p-3">Alternatif produk</th>
                        @foreach ($criteria as $criterion)
                            <th class="p-3 text-right">{{ $criterion['code'] }} · {{ number_format((float) $criterion['weight'], 4, ',', '.') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach ($matrixY as $row)
                        <tr>
                            <td class="p-3 font-bold text-neutral-900">{{ $row['product']['name'] }}</td>
                            @foreach ($criteria as $criterion)
                                <td class="p-3 text-right font-mono">{{ number_format((float) $row[$criterion['code']], 6, ',', '.') }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-xs">
        <div>
            <h2 class="font-heading text-sm font-bold text-neutral-900">4. Solusi ideal positif (A⁺) dan negatif (A⁻)</h2>
            <p class="mt-1 text-xs text-neutral-500">Benefit menggunakan maksimum sebagai A⁺, sementara cost menggunakan minimum. A⁻ memakai nilai sebaliknya.</p>
        </div>
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach ([['label' => 'Solusi ideal positif (A⁺)', 'values' => $idealPositive, 'tone' => 'positive'], ['label' => 'Solusi ideal negatif (A⁻)', 'values' => $idealNegative, 'tone' => 'negative']] as $ideal)
                <div class="rounded-xl border p-4 {{ $ideal['tone'] === 'positive' ? 'border-emerald-200 bg-emerald-50/60' : 'border-amber-200 bg-amber-50/60' }}">
                    <h3 class="text-xs font-bold text-neutral-900">{{ $ideal['label'] }}</h3>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        @foreach ($ideal['values'] as $code => $value)
                            <div class="rounded-lg border border-white bg-white p-2.5">
                                <span class="block font-mono text-[10px] text-neutral-500">{{ $code }}</span>
                                <span class="mt-1 block font-mono text-xs font-bold text-neutral-900">{{ number_format((float) $value, 6, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs">
        <div class="border-b border-neutral-100 p-5">
            <h2 class="font-heading text-sm font-bold text-neutral-900">5–6. Jarak, nilai preferensi, dan ranking akhir</h2>
            <p class="mt-1 text-xs text-neutral-500">Jarak Euclidean dihitung terhadap seluruh kriteria. Nilai preferensi lebih tinggi berarti prioritas relatif lebih tinggi.</p>
            <p class="mt-2 font-mono text-[11px] text-blue-800">Vᵢ = Dᵢ⁻ / (Dᵢ⁺ + Dᵢ⁻)</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-xs">
                <thead class="bg-neutral-50 text-neutral-600">
                    <tr>
                        <th class="p-3">Ranking</th>
                        <th class="p-3">Produk</th>
                        <th class="p-3 text-right">D⁺</th>
                        <th class="p-3 text-right">D⁻</th>
                        <th class="p-3 text-right">Preferensi (Vᵢ)</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach ($preferences as $item)
                        @php($rank = (int) ($item['rank'] ?? 0))
                        <tr class="{{ ($item['rank'] ?? 0) === 1 ? 'bg-blue-50/60' : '' }}">
                            <td class="p-3">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full font-bold {{ $rank === 1 ? 'bg-blue-700 text-white' : 'bg-neutral-100 text-neutral-700' }}">
                                    {{ $rank ?: '—' }}
                                </span>
                            </td>
                            <td class="p-3 font-bold text-neutral-900">{{ $item['product']['name'] }}</td>
                            <td class="p-3 text-right font-mono">{{ number_format((float) ($item['d_plus'] ?? 0), 6, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono">{{ number_format((float) ($item['d_minus'] ?? 0), 6, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono font-bold text-blue-800">{{ number_format((float) $item['ci'], 6, ',', '.') }}</td>
                            <td class="p-3">
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $rank === 1 ? 'bg-blue-100 text-blue-800' : 'bg-neutral-100 text-neutral-700' }}">
                                    {{ $item['recommendation'] ?? ($rank === 1 ? 'Prioritas tertinggi' : 'Prioritas relatif') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="border-t border-neutral-100 px-5 py-4 text-xs leading-5 text-neutral-500">
            Ranking menunjukkan urutan prioritas relatif di antara alternatif yang dinilai. Ranking tidak memastikan stok akan habis dan tidak menentukan jumlah unit yang harus dibeli.
        </p>
    </section>
</div>
@endsection

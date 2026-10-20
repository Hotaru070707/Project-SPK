@extends('layouts.owner', ['title' => 'Kriteria TOPSIS - Mobeli', 'header' => 'Pengaturan Kriteria & Bobot SPK TOPSIS'])

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-neutral-100 gap-4 mb-6">
            <div>
                <h3 class="text-base font-bold font-heading text-neutral-900">Daftar Kriteria Penilaian Toko</h3>
                <p class="text-xs text-neutral-500 mt-0.5">
                    Total bobot wajib 1.00 (100%). Skor setiap kriteria dibentuk agar nilai lebih tinggi berarti prioritas restock lebih tinggi.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-neutral-600">Total Bobot Saat Ini:</span>
                <span class="px-3 py-1 rounded-full text-xs font-extrabold font-mono {{ abs($totalWeight - 1.0) < 0.001 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                    {{ number_format($totalWeight, 4) }}
                </span>
            </div>
        </div>

        <form action="{{ route('admin.topsis.criteria.update') }}" method="POST">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-neutral-50 border-b border-neutral-200 text-neutral-600 uppercase font-semibold">
                        <tr>
                            <th class="p-3">Kode</th>
                            <th class="p-3">Nama Kriteria</th>
                            <th class="p-3">Tipe Atribut</th>
                            <th class="p-3">Sumber Data Toko</th>
                            <th class="p-3 w-40">Bobot ($W$)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach($criteria as $c)
                            <tr>
                                <td class="p-3 font-mono font-bold text-neutral-900">{{ $c->code }}</td>
                                <td class="p-3">
                                    <span class="font-bold text-neutral-900 block">{{ $c->name }}</span>
                                    <span class="text-[11px] text-neutral-400">{{ $c->description }}</span>
                                </td>
                                <td class="p-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase font-mono {{ $c->type === 'benefit' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                                        {{ $c->type }}
                                    </span>
                                </td>
                                <td class="p-3 text-neutral-500 font-mono text-[11px]">
                                    {{ $c->data_source }}
                                </td>
                                <td class="p-3">
                                    <input type="number" step="0.0001" min="0" max="1" 
                                           name="weights[{{ $c->id }}]" value="{{ $c->weight }}" required
                                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg px-2.5 py-1.5 text-xs font-mono font-bold text-neutral-900 focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 pt-4 border-t border-neutral-100 flex items-center justify-between">
                <p class="text-[11px] text-neutral-400">
                    *Kriteria benefit semakin tinggi semakin bagus, cost semakin rendah semakin bagus.
                </p>
                <div class="flex items-center gap-3">
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                        Simpan Perubahan Bobot
                    </button>
                    <a href="{{ route('admin.topsis.calculate') }}" class="px-4 py-2.5 bg-neutral-900 hover:bg-neutral-800 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                        <i data-lucide="play" class="w-3.5 h-3.5 text-emerald-400"></i> Jalankan Perhitungan
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

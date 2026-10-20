@extends('layouts.owner', ['title' => 'Daftar Pelanggan - Mobeli', 'header' => 'Daftar Pelanggan'])

@section('content')
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden shadow-xs">
    <div class="p-4 bg-neutral-50 border-b border-neutral-200">
        <h3 class="font-bold text-xs uppercase tracking-wider text-neutral-700">Daftar Pelanggan Terdaftar</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-neutral-100/70 border-b border-neutral-200 text-neutral-600 uppercase font-semibold">
                <tr>
                    <th class="p-3">Nama Pelanggan</th>
                    <th class="p-3">Email & WhatsApp</th>
                    <th class="p-3 text-center">Total Transaksi</th>
                    <th class="p-3 text-center">Tanggal Daftar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @foreach($customers as $c)
                    <tr class="hover:bg-neutral-50">
                        <td class="p-3 font-bold text-neutral-900">{{ $c->name }}</td>
                        <td class="p-3">
                            <span class="block text-neutral-800">{{ $c->email }}</span>
                            <span class="text-[11px] text-neutral-500 font-mono">{{ $c->phone ?? '-' }}</span>
                        </td>
                        <td class="p-3 text-center font-bold">
                            {{ $c->orders_count }} Kali
                        </td>
                        <td class="p-3 text-center text-neutral-500">
                            {{ $c->created_at->format('d/m/Y') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

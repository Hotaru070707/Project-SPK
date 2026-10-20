@extends('layouts.owner', ['title' => 'Manajemen Pesanan - Mobeli', 'header' => 'Kelola Pesanan & Verifikasi Bukti Pembayaran'])

@section('content')
<div class="space-y-6">
    <div class="bg-white p-5 rounded-2xl border border-neutral-200 shadow-xs flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold font-heading text-neutral-900">Daftar Seluruh Pesanan (Online & POS)</h3>
            <p class="text-xs text-neutral-500">Verifikasi bukti transfer manual pelanggan dan pantau status pengiriman barang.</p>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-100/70 border-b border-neutral-200 text-neutral-600 uppercase font-semibold">
                    <tr>
                        <th class="p-3">No. Invoice</th>
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Tipe</th>
                        <th class="p-3">Pelanggan</th>
                        <th class="p-3 text-right">Total Tagihan</th>
                        <th class="p-3 text-center">Metode</th>
                        <th class="p-3 text-center">Bukti Bayar</th>
                        <th class="p-3 text-center">Status Pembayaran</th>
                        <th class="p-3 text-center">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($orders as $o)
                        <tr class="hover:bg-neutral-50">
                            <td class="p-3 font-mono font-bold text-neutral-900">{{ $o->order_number }}</td>
                            <td class="p-3 text-neutral-500">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $o->type === 'online' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ strtoupper($o->type) }}
                                </span>
                            </td>
                            <td class="p-3 font-medium">{{ $o->customer->name ?? 'Kasir Offline' }}</td>
                            <td class="p-3 text-right font-extrabold text-neutral-900 price-font">
                                Rp {{ number_format($o->total, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded bg-neutral-100 font-mono text-[10px] font-bold">
                                    {{ $o->payment_method }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                @if($o->payment_proof)
                                    <a href="{{ asset('storage/' . $o->payment_proof) }}" target="_blank" class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded text-[10px] font-bold hover:underline inline-flex items-center gap-1">
                                        <i data-lucide="image" class="w-3 h-3"></i> Lihat Foto
                                    </a>
                                @else
                                    <span class="text-neutral-400 text-[10px]">-</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $o->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                                    {{ strtoupper($o->payment_status) }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                @if($o->payment_status !== 'paid')
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form action="{{ route('admin.orders.verify', $o->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] rounded transition">
                                                Setujui Lunas
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.orders.verify', $o->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white font-bold text-[10px] rounded transition">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-emerald-700 font-bold text-[11px] inline-flex items-center gap-1">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i> Terverifikasi
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-neutral-400">Belum ada pesanan masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-neutral-100">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection

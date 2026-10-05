@extends('layouts.cashier', ['title' => 'Riwayat Transaksi Kasir - Mobeli'])

@section('content')
<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-6">
    <div class="border-b border-slate-200 pb-4 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight">Riwayat Transaksi Offline POS</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar struk transaksi penjualan kasir offline.</p>
        </div>
        <a href="{{ route('cashier.pos') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
            + Transaksi Baru
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-100/70 border-b border-slate-200 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="p-3">No. Struk</th>
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Pelanggan</th>
                        <th class="p-3">Item Belanja</th>
                        <th class="p-3 text-right">Subtotal</th>
                        <th class="p-3 text-right">Diskon</th>
                        <th class="p-3 text-right">Total</th>
                        <th class="p-3 text-center">Metode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $o)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 font-mono font-bold text-slate-900">{{ $o->order_number }}</td>
                            <td class="p-3 text-slate-500">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3 font-medium">{{ $o->customer->name ?? 'Pelanggan Umum' }}</td>
                            <td class="p-3 text-slate-600">
                                @foreach($o->items as $it)
                                    <span class="inline-block bg-slate-100 px-1.5 py-0.5 rounded text-[10px] mr-1">
                                        {{ $it->product_name }} ({{ $it->qty }})
                                    </span>
                                @endforeach
                            </td>
                            <td class="p-3 text-right price-font">Rp {{ number_format($o->subtotal, 0, ',', '.') }}</td>
                            <td class="p-3 text-right price-font text-emerald-700">- Rp {{ number_format($o->discount, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-bold text-emerald-700 price-font">
                                Rp {{ number_format($o->total, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-[10px] font-bold uppercase font-mono">
                                    {{ $o->payment_method }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">
                                Belum ada transaksi kasir.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection

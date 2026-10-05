@extends('layouts.cashier', ['title' => 'Dashboard Kasir - Mobeli'])

@section('content')
<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-6">
    <!-- Top Welcome Card & Shift Status -->
    <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Portal Kasir Offline</span>
            <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight mt-0.5">
                Selamat Datang, {{ auth()->user()->name }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">Layani transaksi sembako kasir secara cepat dan tercatat otomatis ke database.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('cashier.pos') }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
                <i data-lucide="calculator" class="w-4 h-4"></i> Buka Layar Kasir
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-medium block">Total Omset Kasir Hari Ini</span>
            <div class="text-2xl font-extrabold text-slate-900 price-font mt-1">
                Rp {{ number_format($totalOmsetToday, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-emerald-600 font-bold mt-1 inline-block">Transaksi Berhasil</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-medium block">Jumlah Transaksi Hari Ini</span>
            <div class="text-2xl font-extrabold text-slate-900 price-font mt-1">
                {{ $totalTrxToday }} Struk
            </div>
            <span class="text-[10px] text-slate-400 mt-1 inline-block">Tercatat di sistem</span>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-xs uppercase tracking-wider text-slate-700">Transaksi Kasir Terbaru</h3>
            <a href="{{ route('cashier.transactions') }}" class="text-xs text-emerald-600 font-semibold hover:underline">
                Lihat Semua
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-100/70 border-b border-slate-200 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="p-3">No. Invoice</th>
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Pelanggan</th>
                        <th class="p-3 text-right">Total Belanja</th>
                        <th class="p-3 text-center">Metode</th>
                        <th class="p-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($todayOrders->take(6) as $order)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 font-mono font-bold text-slate-900">{{ $order->order_number }}</td>
                            <td class="p-3 text-slate-500">{{ $order->created_at->format('H:i') }}</td>
                            <td class="p-3 font-medium">{{ $order->customer->name ?? 'Pelanggan Umum' }}</td>
                            <td class="p-3 text-right font-bold text-emerald-700 price-font">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-[10px] font-bold uppercase font-mono">
                                    {{ $order->payment_method }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                    {{ strtoupper($order->order_status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                Belum ada transaksi kasir hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

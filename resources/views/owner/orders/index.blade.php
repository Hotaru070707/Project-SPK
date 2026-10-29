@extends('layouts.owner', ['title' => 'Pesanan & Transaksi - Mobeli', 'header' => 'Kelola Pesanan & Transaksi'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-extrabold font-heading text-neutral-900 dark:text-white">Kelola Pesanan & Transaksi</h1>
            <p class="mt-1 text-sm text-neutral-500 dark:text-slate-400">Pantau pesanan online dan transaksi POS, serta kelola status pembayaran dan proses pesanan dalam satu halaman.</p>
        </div>
    </div>

    @if($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200">
            <p class="font-bold">Permintaan belum dapat diproses.</p>
            <ul class="mt-1 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section aria-label="Ringkasan pesanan" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Total Pesanan', 'value' => $summary['total'], 'icon' => 'shopping-bag', 'color' => 'blue'],
            ['label' => 'Menunggu Pembayaran', 'value' => $summary['awaiting_payment'], 'icon' => 'clock-3', 'color' => 'amber'],
            ['label' => 'Sedang Diproses', 'value' => $summary['processing'], 'icon' => 'package-check', 'color' => 'indigo'],
            ['label' => 'Pesanan Selesai', 'value' => $summary['completed'], 'icon' => 'check-circle-2', 'color' => 'emerald'],
        ] as $card)
            <article class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-neutral-500 dark:text-slate-400">{{ $card['label'] }}</p>
                    <i data-lucide="{{ $card['icon'] }}" class="h-4 w-4 text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400"></i>
                </div>
                <p class="mt-2 font-mono text-2xl font-black text-neutral-900 dark:text-white">{{ number_format($card['value']) }}</p>
            </article>
        @endforeach
    </section>

    <form method="GET" action="{{ route('admin.orders') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900 sm:grid-cols-2 xl:grid-cols-4">
        <label class="xl:col-span-2">
            <span class="mb-1 block text-xs font-semibold text-neutral-700 dark:text-slate-300">Cari invoice atau pelanggan</span>
            <input name="search" type="search" value="{{ $validated['search'] ?? '' }}" maxlength="100" placeholder="Nomor invoice, nama, atau catatan pelanggan"
                class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </label>
        <label>
            <span class="mb-1 block text-xs font-semibold text-neutral-700 dark:text-slate-300">Jenis transaksi</span>
            <select name="type" class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <option value="">Semua jenis</option>
                <option value="online" @selected(($validated['type'] ?? '') === 'online')>Online</option>
                <option value="offline" @selected(($validated['type'] ?? '') === 'offline')>POS</option>
            </select>
        </label>
        <label>
            <span class="mb-1 block text-xs font-semibold text-neutral-700 dark:text-slate-300">Status pembayaran</span>
            <select name="payment_status" class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <option value="">Semua status pembayaran</option>
                @foreach(['unpaid' => 'Belum Dibayar', 'pending' => 'Menunggu Pembayaran', 'paid' => 'Lunas', 'expired' => 'Kedaluwarsa', 'refunded' => 'Dikembalikan'] as $value => $label)
                    <option value="{{ $value }}" @selected(($validated['payment_status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="mb-1 block text-xs font-semibold text-neutral-700 dark:text-slate-300">Status pesanan</span>
            <select name="order_status" class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <option value="">Semua status pesanan</option>
                @foreach($orderStatusLabels as $value => $label)
                    @if($value !== 'void')
                    <option value="{{ $value }}" @selected(($validated['order_status'] ?? '') === $value)>{{ $label }}</option>
                    @endif
                @endforeach
            </select>
        </label>
        <label>
            <span class="mb-1 block text-xs font-semibold text-neutral-700 dark:text-slate-300">Dari tanggal</span>
            <input name="start_date" type="date" value="{{ $validated['start_date'] ?? '' }}" class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </label>
        <label>
            <span class="mb-1 block text-xs font-semibold text-neutral-700 dark:text-slate-300">Sampai tanggal</span>
            <input name="end_date" type="date" value="{{ $validated['end_date'] ?? '' }}" class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </label>
        <div class="flex items-end gap-2">
            <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-neutral-900 px-4 py-2 text-xs font-bold text-white hover:bg-neutral-700 dark:bg-blue-600 dark:hover:bg-blue-700">
                <i data-lucide="filter" class="h-4 w-4"></i> Terapkan
            </button>
            <a href="{{ route('admin.orders') }}" class="rounded-lg border border-neutral-300 px-4 py-2 text-xs font-bold text-neutral-700 hover:bg-neutral-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Reset</a>
        </div>
    </form>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-neutral-200 bg-neutral-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/70">
            <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-slate-200">
                Daftar Semua Transaksi
            </h2>
            <span class="text-xs text-neutral-500 dark:text-slate-400">{{ $orders->total() }} transaksi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-left text-xs">
                <thead class="border-b border-neutral-200 bg-neutral-50/70 text-[10px] uppercase tracking-wide text-neutral-500 dark:border-slate-800 dark:bg-slate-800/40 dark:text-slate-400">
                    <tr>
                        <th class="p-3">Invoice / Tanggal</th>
                        <th class="p-3">Pelanggan / Kasir</th>
                        <th class="p-3">Jenis</th>
                        <th class="p-3 text-right">Total</th>
                        <th class="p-3">Metode</th>
                        <th class="p-3 text-center">Pembayaran</th>
                        <th class="p-3 text-center">Pesanan</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-800">
                    @forelse($orders as $order)
                        @php
                            $canConfirmTransfer = $order->type === 'online'
                                && $order->payment_method === 'manual_transfer'
                                && in_array($order->payment_status, ['unpaid', 'pending'], true)
                                && $order->order_status === 'pending_payment';
                        @endphp
                        <tr class="align-top hover:bg-neutral-50/80 dark:hover:bg-slate-800/40">
                            <td class="p-3">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-blue-700 hover:underline dark:text-blue-300">{{ $order->order_number }}</a>
                                <p class="mt-1 whitespace-nowrap text-[11px] text-neutral-500 dark:text-slate-400">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                            </td>
                            <td class="p-3">
                                <p class="font-semibold text-neutral-900 dark:text-white">{{ $order->customer?->name ?? ($order->type === 'offline' ? 'Pelanggan POS' : 'Pelanggan umum') }}</p>
                                @if($order->cashier)
                                    <p class="mt-1 text-[10px] text-neutral-500 dark:text-slate-400">Kasir: {{ $order->cashier->name }}</p>
                                @endif
                            </td>
                            <td class="p-3">
                                <span class="rounded-full px-2 py-1 text-[10px] font-bold {{ $order->type === 'online' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                    {{ $order->type === 'online' ? 'ONLINE' : 'POS' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap p-3 text-right font-mono font-bold text-neutral-900 dark:text-white">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                            <td class="p-3 text-neutral-700 dark:text-slate-300">{{ $paymentMethodLabels[$order->payment_method] ?? strtoupper($order->payment_method) }}</td>
                            <td class="p-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold {{ in_array($order->payment_status, ['expired', 'refunded'], true) ? 'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300' : ($order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300') }}">
                                    {{ $order->payment_status_label }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">{{ $orderStatusLabels[$order->order_status] ?? strtoupper($order->order_status) }}</span>
                            </td>
                            <td class="p-3">
                                <div class="flex flex-col items-center gap-2">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="rounded-lg border border-neutral-300 px-2.5 py-1.5 font-bold text-neutral-700 hover:bg-neutral-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Detail</a>
                                    @if($canConfirmTransfer)
                                        <form action="{{ route('admin.orders.payment.confirm', $order) }}" method="POST" onsubmit="return confirm('Pastikan pembayaran sudah masuk dan nominalnya sesuai pada mutasi rekening. Konfirmasi pesanan ini sebagai lunas?')">
                                            @csrf
                                            <button type="submit" class="whitespace-nowrap rounded-lg bg-emerald-600 px-2.5 py-1.5 font-bold text-white hover:bg-emerald-700">Konfirmasi Pembayaran</button>
                                        </form>
                                        <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Batalkan pesanan ini dan lepaskan reservasi stoknya?')">
                                            @csrf
                                            <button type="submit" class="whitespace-nowrap rounded-lg border border-red-200 px-2.5 py-1.5 font-bold text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950/40">Batalkan Pesanan</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center">
                                <i data-lucide="inbox" class="mx-auto h-8 w-8 text-neutral-300 dark:text-slate-600"></i>
                                <p class="mt-2 text-sm font-semibold text-neutral-600 dark:text-slate-300">Tidak ada transaksi yang sesuai.</p>
                                <p class="mt-1 text-xs text-neutral-400">Ubah filter atau periksa kembali rentang tanggal.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-neutral-200 p-4 dark:border-slate-800">{{ $orders->links() }}</div>
    </section>
</div>
@endsection

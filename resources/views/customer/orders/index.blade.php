@extends('layouts.app', ['title' => 'Riwayat Pemesanan - Mobeli'])

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <nav class="text-xs text-neutral-500 dark:text-slate-400 mb-2" aria-label="Breadcrumb">
                <a href="{{ route('profile.edit') }}" class="hover:text-emerald-600">Profil Saya</a>
                <span class="mx-1">/</span>
                <span class="font-semibold text-neutral-800 dark:text-slate-200">Riwayat Pemesanan</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-neutral-900 dark:text-white">Riwayat Pemesanan</h1>
            <p class="mt-1 text-sm text-neutral-500 dark:text-slate-400">Pantau pesanan online dan status terbaru yang tercatat di sistem.</p>
        </div>
        <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 transition">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i> Kembali Berbelanja
        </a>
    </div>

    <nav aria-label="Navigasi profil" class="flex flex-wrap gap-2">
        <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 rounded-full border border-neutral-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-2.5 text-sm font-semibold text-neutral-700 dark:text-slate-200 hover:border-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-400 transition">
            <i data-lucide="user-round" class="w-4 h-4"></i> Informasi Profil
        </a>
        <a href="{{ route('orders.index') }}" aria-current="page" class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">
            <i data-lucide="receipt-text" class="w-4 h-4"></i> Riwayat Pemesanan
        </a>
    </nav>

    <form method="GET" action="{{ route('orders.index') }}" class="grid grid-cols-1 sm:grid-cols-[1fr_220px_auto] gap-3 rounded-2xl border border-neutral-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
        <label class="relative">
            <span class="sr-only">Cari nomor pesanan</span>
            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400"></i>
            <input type="search" name="search" value="{{ request('search') }}" maxlength="40" placeholder="Cari nomor pesanan"
                class="w-full rounded-full border border-neutral-200 dark:border-slate-700 bg-white dark:bg-slate-800 pl-11 pr-4 py-2.5 text-sm text-neutral-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
        </label>
        <label>
            <span class="sr-only">Filter status pesanan</span>
            <select name="status" class="w-full rounded-full border border-neutral-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm text-neutral-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                <option value="">Semua status</option>
                @foreach($orderStatusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full bg-neutral-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-neutral-800 dark:bg-blue-600 dark:hover:bg-blue-700 transition">
            Terapkan
        </button>
    </form>

    @if($orders->isEmpty())
        <section class="rounded-3xl border border-dashed border-neutral-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-6 py-14 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300">
                <i data-lucide="package-open" class="w-7 h-7"></i>
            </div>
            <h2 class="mt-4 text-lg font-bold text-neutral-900 dark:text-white">
                {{ request()->filled('search') || request()->filled('status') ? 'Pesanan tidak ditemukan' : 'Kamu belum memiliki riwayat pemesanan.' }}
            </h2>
            <p class="mt-2 text-sm text-neutral-500 dark:text-slate-400">
                {{ request()->filled('search') || request()->filled('status') ? 'Coba ubah kata pencarian atau filter status.' : 'Pesanan online yang kamu buat akan muncul di halaman ini.' }}
            </p>
            <a href="{{ route('catalog') }}" class="mt-5 inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 transition">
                Mulai Berbelanja <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </section>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                @php
                    $firstItem = $order->items->first();
                    $orderStatusClass = match($order->order_status) {
                        'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300',
                        'cancelled', 'void' => 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300',
                        'pending_payment' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
                        default => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
                    };
                    $paymentStatusClass = $order->payment_status === 'paid'
                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                        : (in_array($order->payment_status, ['expired', 'refunded'], true)
                            ? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                            : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300');
                @endphp
                <article class="rounded-2xl border border-neutral-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-neutral-100 dark:border-slate-800 pb-4">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-neutral-400">Nomor Pesanan</p>
                            <h2 class="mt-1 font-mono text-base font-extrabold text-neutral-900 dark:text-white">{{ $order->order_number }}</h2>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-slate-400">{{ $order->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full px-3 py-1 text-[11px] font-bold {{ $paymentStatusClass }}">
                                Pembayaran: {{ $paymentStatusLabels[$order->payment_status] ?? strtoupper($order->payment_status) }}
                            </span>
                            <span class="rounded-full px-3 py-1 text-[11px] font-bold {{ $orderStatusClass }}">
                                {{ $orderStatusLabels[$order->order_status] ?? strtoupper($order->order_status) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4">
                        <div class="flex min-w-0 items-center gap-3">
                            @if($firstItem?->product?->image_url)
                                <img src="{{ $firstItem->product->image_url }}" alt="" class="h-14 w-14 shrink-0 rounded-xl border border-neutral-200 dark:border-slate-700 object-cover">
                            @else
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-neutral-100 dark:bg-slate-800 text-neutral-400">
                                    <i data-lucide="package" class="w-6 h-6"></i>
                                </div>
                            @endif
                            <div class="min-w-0">
                                @if($firstItem)
                                    <p class="truncate text-sm font-semibold text-neutral-900 dark:text-white">{{ $firstItem->product_name }}</p>
                                    @if($order->items->count() > 1)
                                        <p class="mt-0.5 text-xs text-neutral-500 dark:text-slate-400">dan {{ $order->items->count() - 1 }} produk lainnya</p>
                                    @endif
                                @else
                                    <p class="text-sm font-semibold text-neutral-700 dark:text-slate-300">Rincian produk tidak tersedia</p>
                                @endif
                                <p class="mt-1 text-xs text-neutral-500 dark:text-slate-400">{{ $order->items->sum('qty') }} barang · {{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-5">
                            <div class="sm:text-right">
                                <p class="text-[11px] text-neutral-500 dark:text-slate-400">Total pembayaran</p>
                                <p class="mt-0.5 font-mono text-base font-extrabold text-emerald-700 dark:text-emerald-400">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</p>
                            </div>
                            <a href="{{ route('orders.show', $order->order_number) }}" class="inline-flex shrink-0 items-center gap-2 rounded-full border border-emerald-600 px-4 py-2 text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-slate-800 transition">
                                Lihat Detail <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="pt-2">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection

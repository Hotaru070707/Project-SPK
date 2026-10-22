@extends('layouts.cashier', ['title' => 'Detail Pesanan Online - Mobeli'])

@section('content')
<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-6">
    <style>
        @media print {
            body > header,
            .print-hidden {
                display: none !important;
            }

            body,
            main {
                background: #fff !important;
                color: #0f172a !important;
            }

            .print-card {
                box-shadow: none !important;
                break-inside: avoid;
            }
        }
    </style>

    <nav class="text-xs text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('cashier.index') }}" class="hover:text-blue-700">Dashboard Kasir</a>
        <span class="mx-1">/</span>
        <a href="{{ route('cashier.transactions') }}" class="hover:text-blue-700">Pesanan Online</a>
        <span class="mx-1">/</span>
        <span class="font-semibold text-slate-800">Detail Pesanan</span>
    </nav>

    <section class="print-card bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Invoice Pesanan Online</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold font-mono tracking-tight text-slate-900">
                    {{ $order->order_number }}
                </h1>
                <p class="mt-2 text-sm text-slate-500">Dipesan {{ $order->created_at->format('d M Y, H:i') }}</p>
            </div>
            <div class="print-hidden flex flex-wrap gap-2">
                <a href="{{ route('cashier.transactions') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-semibold">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold">
                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak Struk
                </button>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Status Pembayaran</p>
                <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $paymentStatusLabels[$order->payment_status] ?? strtoupper($order->payment_status) }}
                </span>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Status Pesanan</p>
                <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold bg-blue-100 text-blue-800">
                    {{ $orderStatusLabels[$order->order_status] ?? strtoupper($order->order_status) }}
                </span>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <section class="print-card bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="user-round" class="w-4 h-4 text-blue-600"></i> Informasi Pelanggan
            </h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-slate-500">Nama</dt>
                    <dd class="mt-0.5 font-semibold text-slate-900">{{ $order->customer?->name ?? 'Data pelanggan tidak tersedia' }}</dd>
                </div>
                @if($order->customer?->email)
                    <div>
                        <dt class="text-xs text-slate-500">Email</dt>
                        <dd class="mt-0.5 text-slate-800 break-all">{{ $order->customer->email }}</dd>
                    </div>
                @endif
                @if($order->customer?->phone)
                    <div>
                        <dt class="text-xs text-slate-500">Telepon akun</dt>
                        <dd class="mt-0.5 text-slate-800">{{ $order->customer->phone }}</dd>
                    </div>
                @endif
                @if($order->address)
                    <div>
                        <dt class="text-xs text-slate-500">Alamat</dt>
                        <dd class="mt-0.5 text-slate-800">
                            {{ $order->address->recipient_name }} — {{ $order->address->full_address }}
                            @if($order->address->city)
                                , {{ $order->address->city }}
                            @endif
                            @if($order->address->postal_code)
                                {{ $order->address->postal_code }}
                            @endif
                        </dd>
                    </div>
                @endif
                @if($order->notes)
                    <div>
                        <dt class="text-xs text-slate-500">Catatan pesanan</dt>
                        <dd class="mt-0.5 text-slate-800 whitespace-pre-line">{{ $order->notes }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="print-card bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 lg:col-span-2">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="package" class="w-4 h-4 text-blue-600"></i> Barang yang Dipesan
            </h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm text-left">
                    <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-3 pr-3">No.</th>
                            <th class="py-3 pr-3">Barang</th>
                            <th class="py-3 pr-3">SKU / Kode</th>
                            <th class="py-3 pr-3 text-right">Harga</th>
                            <th class="py-3 pr-3 text-center">Jumlah</th>
                            <th class="py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="py-3 pr-3 text-slate-500">{{ $loop->iteration }}</td>
                                <td class="py-3 pr-3">
                                    <div class="flex items-center gap-3">
                                        @if($item->product?->image_url)
                                            <img src="{{ $item->product->image_url }}" alt="" class="w-10 h-10 rounded-lg object-cover border border-slate-200">
                                        @endif
                                        <span class="font-semibold text-slate-900">{{ $item->product_name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 pr-3 text-slate-600">{{ $item->product?->barcode ?? '—' }}</td>
                                <td class="py-3 pr-3 text-right whitespace-nowrap">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                <td class="py-3 pr-3 text-center">{{ $item->qty }}</td>
                                <td class="py-3 text-right font-semibold whitespace-nowrap">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <section class="print-card bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 lg:col-span-2">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="credit-card" class="w-4 h-4 text-blue-600"></i> Rincian Pembayaran
            </h2>
            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs text-slate-500">Metode pembayaran</dt>
                    <dd class="mt-0.5 font-semibold text-slate-900">{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</dd>
                </div>
                @if($order->paid_at)
                    <div>
                        <dt class="text-xs text-slate-500">Waktu pembayaran</dt>
                        <dd class="mt-0.5 text-slate-800">{{ $order->paid_at->format('d M Y, H:i') }}</dd>
                    </div>
                @endif
                @if($order->midtrans_order_id)
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-slate-500">Referensi Midtrans</dt>
                        <dd class="mt-0.5 font-mono text-slate-800 break-all">{{ $order->midtrans_order_id }}</dd>
                    </div>
                @endif
            </dl>
            <div class="mt-5 border-t border-slate-200 pt-4 space-y-2 text-sm">
                <div class="flex justify-between gap-4 text-slate-600">
                    <span>Subtotal barang</span>
                    <span class="font-medium text-slate-900">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between gap-4 text-slate-600">
                    <span>Diskon</span>
                    <span class="font-medium text-slate-900">− Rp {{ number_format((float) $order->discount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between gap-4 border-t border-slate-200 pt-3 text-base font-extrabold text-slate-900">
                    <span>Total pembayaran</span>
                    <span class="text-blue-700">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </section>

        <section class="print-card bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="clipboard-check" class="w-4 h-4 text-blue-600"></i> Proses Pesanan
            </h2>
            <div class="mt-4 text-sm text-slate-600">
                @if($order->payment_status !== 'paid')
                    <p>Pembayaran belum dikonfirmasi. Status pesanan tidak dapat dilanjutkan oleh kasir.</p>
                @elseif($order->order_status === 'processing')
                    <p>Pesanan sedang diproses. Setelah barang siap, tandai agar dapat diserahkan kepada pelanggan.</p>
                @elseif($order->order_status === 'packed')
                    <p>Pesanan siap diambil. Konfirmasikan hanya setelah barang benar-benar diserahkan kepada pelanggan.</p>
                @elseif($order->order_status === 'completed')
                    <p>Pesanan telah selesai diserahkan. Tidak ada tindakan lanjutan yang tersedia.</p>
                    <p class="mt-2 text-xs text-slate-500">Diselesaikan pada {{ $order->updated_at->format('d M Y, H:i') }}.</p>
                @elseif(in_array($order->order_status, ['cancelled', 'void'], true))
                    <p>Pesanan dibatalkan atau dibatalkan secara sistem. Pesanan tidak dapat diproses kembali melalui halaman ini.</p>
                @else
                    <p>Status saat ini tidak menyediakan tindakan kasir.</p>
                @endif
            </div>

            @if($order->payment_status === 'paid' && $order->order_status === 'processing')
                <form method="POST" action="{{ route('cashier.orders.ready', $order->order_number) }}" class="print-hidden mt-5" onsubmit="return confirm('Tandai pesanan ini siap diambil?')">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-3 text-sm font-bold text-white">
                        <i data-lucide="package-check" class="w-4 h-4"></i> Tandai Siap Diambil
                    </button>
                </form>
            @elseif($order->payment_status === 'paid' && $order->order_status === 'packed')
                <form method="POST" action="{{ route('cashier.orders.complete', $order->order_number) }}" class="print-hidden mt-5" onsubmit="return confirm('Konfirmasi bahwa barang telah benar-benar diserahkan kepada pelanggan? Pesanan akan ditandai selesai.')">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-3 text-sm font-bold text-white">
                        <i data-lucide="check-check" class="w-4 h-4"></i> Konfirmasi Penyerahan
                    </button>
                </form>
            @endif

            @if($order->cashier)
                <p class="mt-4 border-t border-slate-200 pt-3 text-xs text-slate-500">
                    Petugas penyelesaian: <span class="font-semibold text-slate-700">{{ $order->cashier->name }}</span>
                </p>
            @endif
        </section>
    </div>

    <footer class="print-card text-center text-xs text-slate-500">
        <p class="font-extrabold tracking-widest text-slate-800">MOBELI</p>
        <p>Terima kasih telah berbelanja.</p>
    </footer>
</div>
@endsection

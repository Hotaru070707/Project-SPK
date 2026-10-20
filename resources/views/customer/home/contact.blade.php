@extends('layouts.app', ['title' => 'Hubungi Kami - Mobeli'])

@section('content')
<div class="py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-3xl border border-neutral-200 p-8 sm:p-12 shadow-xs">
        <div class="text-center max-w-md mx-auto mb-10">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Hubungi Kami</span>
            <h1 class="text-2xl sm:text-3xl font-bold font-heading text-neutral-900 tracking-tight mt-1">
                Ada Pertanyaan atau Pesanan Khusus?
            </h1>
            <p class="text-xs sm:text-sm text-neutral-500 mt-2">
                Hubungi kami langsung via WhatsApp untuk respon cepat pengiriman pesanan atau ketersediaan stok sembako.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div class="space-y-4">
                <a href="https://wa.me/6281234567890" target="_blank" 
                   class="p-4 rounded-2xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <i data-lucide="message-circle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-emerald-900 block group-hover:underline">Chat WhatsApp Resmi</span>
                        <span class="text-sm font-extrabold text-neutral-900 font-mono">0812-3456-7890</span>
                        <p class="text-[11px] text-neutral-500">Respon dalam < 5 menit</p>
                    </div>
                </a>

                <div class="p-4 rounded-2xl bg-neutral-50 border border-neutral-200 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-neutral-800 text-white flex items-center justify-center shrink-0">
                        <i data-lucide="map-pin" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-neutral-900 block">Kunjungi Toko Fisik</span>
                        <span class="text-xs text-neutral-600">Jl. Merdeka No. 45, RT 03/RW 02, Sukajadi, Kota Bandung</span>
                    </div>
                </div>
            </div>

            <!-- Pesan Singkat -->
            <div class="p-6 rounded-2xl bg-neutral-50 border border-neutral-200">
                <h3 class="text-sm font-bold text-neutral-900 mb-3">Kirim Pesan Cepat</h3>
                <form onsubmit="event.preventDefault(); alert('Terima kasih! Pesan Anda telah terkirim ke customer care Mobeli.');" class="space-y-3">
                    <div>
                        <input type="text" placeholder="Nama Anda" required 
                               class="w-full bg-white border border-neutral-200 rounded-lg px-3 py-2 text-xs text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary/40">
                    </div>
                    <div>
                        <input type="tel" placeholder="Nomor WhatsApp" required 
                               class="w-full bg-white border border-neutral-200 rounded-lg px-3 py-2 text-xs text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary/40">
                    </div>
                    <div>
                        <textarea placeholder="Tuliskan pertanyaan atau kebutuhan barang..." rows="3" required
                                  class="w-full bg-white border border-neutral-200 rounded-lg px-3 py-2 text-xs text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary/40"></textarea>
                    </div>
                    <button type="submit" 
                            class="w-full py-2.5 bg-primary hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                        Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

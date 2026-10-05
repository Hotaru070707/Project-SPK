@extends('layouts.app', ['title' => 'Tentang Toko - Mobeli'])

@section('content')
<div class="py-12 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-3xl border border-neutral-200 p-8 sm:p-12 shadow-xs space-y-8">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Profil Toko</span>
            <h1 class="text-3xl font-extrabold font-heading text-neutral-900 tracking-tight mt-1">
                Toko Kelontong Berkah (Mobeli)
            </h1>
            <p class="text-sm text-neutral-500 mt-2 leading-relaxed">
                Melayani kebutuhan sembako dan keperluan harian masyarakat dengan harga terjangkau, timbangan jujur, dan pelayanan ramah sejak 2020.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-neutral-100">
            <div class="p-4 rounded-2xl bg-neutral-50 border border-neutral-200/80">
                <i data-lucide="map-pin" class="w-6 h-6 text-emerald-600 mb-2"></i>
                <h3 class="font-bold text-sm text-neutral-900">Alamat Toko</h3>
                <p class="text-xs text-neutral-600 mt-1">Jl. Merdeka No. 45, RT 03/RW 02, Sukajadi, Kota Bandung</p>
            </div>

            <div class="p-4 rounded-2xl bg-neutral-50 border border-neutral-200/80">
                <i data-lucide="clock" class="w-6 h-6 text-emerald-600 mb-2"></i>
                <h3 class="font-bold text-sm text-neutral-900">Jam Operasional</h3>
                <p class="text-xs text-neutral-600 mt-1">Senin - Minggu: 07:00 - 21:30 WIB (Buka Setiap Hari)</p>
            </div>

            <div class="p-4 rounded-2xl bg-neutral-50 border border-neutral-200/80">
                <i data-lucide="phone-call" class="w-6 h-6 text-emerald-600 mb-2"></i>
                <h3 class="font-bold text-sm text-neutral-900">Layanan Pelanggan</h3>
                <p class="text-xs text-neutral-600 mt-1">WhatsApp: 0812-3456-7890<br>Email: halo@mobeli.id</p>
            </div>
        </div>

        <div class="pt-6 border-t border-neutral-100">
            <h2 class="text-lg font-bold font-heading text-neutral-900 mb-3">Tentang Sistem Pendukung Keputusan (TOPSIS)</h2>
            <p class="text-xs sm:text-sm text-neutral-600 leading-relaxed">
                Aplikasi ini dilengkapi algoritma TOPSIS (Technique for Order Preference by Similarity to Ideal Solution) untuk mengolah 6 kriteria riil toko: volume penjualan, risiko dead stock, rasio urgensi restock, loyalitas pelanggan, margin keuntungan, dan tingkat retur. Hasil analisis memberikan rekomendasi objektif bagi pemilik toko dalam menentukan pengadaan barang dan manajemen stok secara terukur.
            </p>
        </div>
    </div>
</div>
@endsection

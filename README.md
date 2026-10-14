# Mobeli

Mobeli adalah aplikasi toko kelontong berbasis Laravel untuk mengelola katalog, stok, penjualan online, transaksi kasir, laporan, dan rekomendasi restock menggunakan metode TOPSIS. Antarmuka menggunakan identitas biru Mobeli.

## Kebutuhan

- PHP 8.3 atau lebih baru
- Composer
- Node.js dan npm
- MySQL 8.0+ (atau MariaDB) dan ekstensi PHP `pdo_mysql`

## Menjalankan secara lokal (Windows PowerShell)

Dari folder `Project-SPK`, jalankan:

```powershell
composer install
if (!(Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Pastikan server MySQL berjalan dan buat database `project-spk` terlebih dahulu (misalnya melalui phpMyAdmin atau dengan menjalankan `CREATE DATABASE \`project-spk\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`). Periksa `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env` agar sesuai dengan server Anda sebelum menjalankan migrasi. Buka alamat lokal yang ditampilkan oleh `php artisan serve` (biasanya `http://127.0.0.1:8000`).

## Akun demo

Seeder menyediakan akun berikut. Semua akun demo menggunakan kata sandi `password123`.

| Peran | Email |
|---|---|
| Owner | `owner@tokotopsis.id` |
| Kasir | `kasir@tokotopsis.id` |
| Pembeli | `budi@gmail.com` |

Ganti kata sandi demo sebelum aplikasi digunakan di lingkungan publik.

## Modul utama

- **Toko pembeli:** katalog, pencarian, kategori, keranjang, checkout, pesanan, dan profil.
- **Kasir:** POS, validasi stok, pembayaran tunai atau nontunai, struk, dan riwayat transaksi.
- **Owner:** dashboard berbasis data, produk dan kategori, pesanan, pelanggan, serta analisis TOPSIS.
- **Audit stok:** transaksi POS mencatat perubahan stok; perubahan stok lainnya dapat ditelusuri melalui catatan pergerakan stok.
- **TOPSIS:** matriks keputusan, normalisasi, matriks terbobot, solusi ideal, jarak, skor preferensi, ranking, dan snapshot hasil.

Owner dan kasir harus masuk menggunakan akun. Katalog pembeli dapat dilihat tanpa akun. Halaman owner dan POS dibatasi berdasarkan peran di sisi server.

## Kriteria dan perhitungan TOPSIS

Produk aktif menjadi alternatif. Data penjualan dihitung dari transaksi lunas dalam 30 hari terakhir. Semua nilai kriteria disusun agar nilai yang lebih tinggi selalu berarti prioritas restock yang lebih tinggi.

| Kode | Kriteria | Bobot awal | Nilai yang dihitung |
|---|---|---:|---|
| C1 | Urgensi stok | 40% | Jika stok menyentuh/di bawah minimum: `(minimum + 1 - stok) / (minimum + 1)`; selain itu 0. |
| C2 | Kecepatan penjualan | 30% | Jumlah unit terjual dalam 30 hari terakhir. |
| C3 | Lama pengadaan | 20% | Waktu tunggu pemasok dalam hari, diisi pada data produk. |
| C4 | Margin keuntungan | 10% | `((harga jual - harga modal) / harga jual) × 100%`. |

Langkah perhitungan:

1. Normalisasi setiap kolom: `r_ij = x_ij / √(Σ x_ij²)`.
2. Kalikan hasil normalisasi dengan bobot: `y_ij = w_j × r_ij`.
3. Karena nilai lebih tinggi selalu lebih mendesak, solusi ideal positif adalah nilai maksimum tiap kolom dan solusi ideal negatif adalah nilai minimumnya.
4. Hitung jarak Euclidean ke kedua solusi: `D+` dan `D-`.
5. Hitung nilai preferensi: `Ci = D- / (D+ + D-)`, lalu urutkan dari nilai tertinggi.

Jika waktu pengadaan suatu produk belum diisi, perhitungan dihentikan dan nama produk yang datanya kurang ditampilkan. Nilai TOPSIS adalah rekomendasi untuk owner, bukan pemesanan otomatis.

## Pengujian

Jalankan seluruh pengujian:

```powershell
php artisan test
```

Pengujian fitur mencakup transaksi POS dan stok, pembatasan akses owner/kasir, validasi bobot TOPSIS, hasil ranking, data yang belum lengkap, serta seeder akun dan produk demo.

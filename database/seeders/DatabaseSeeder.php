<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promo;
use App\Models\TopsisCriterion;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Shift;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SEED USERS (Owner, Kasir, Pelanggan)
        $owner = User::updateOrCreate(['email' => 'owner@tokotopsis.id'], [
            'name' => 'Bapak Hendra (Pemilik)',
            'password' => Hash::make('password123'),
            'phone' => '081234567890',
            'role' => 'owner',
            'member_tier' => 'none',
            'loyalty_points' => 0,
            'is_active' => true,
        ]);

        $cashier = User::updateOrCreate(['email' => 'kasir@tokotopsis.id'], [
            'name' => 'Dewi Lestari (Kasir)',
            'password' => Hash::make('password123'),
            'phone' => '082198765432',
            'role' => 'cashier',
            'member_tier' => 'none',
            'loyalty_points' => 0,
            'is_active' => true,
        ]);

        $customer1 = User::updateOrCreate(['email' => 'budi@gmail.com'], [
            'name' => 'Pak Budi Santoso',
            'password' => Hash::make('password123'),
            'phone' => '081377889900',
            'role' => 'customer',
            'member_tier' => 'silver',
            'loyalty_points' => 150,
            'is_active' => true,
        ]);

        $customer2 = User::updateOrCreate(['email' => 'siti@gmail.com'], [
            'name' => 'Bu Siti Aminah',
            'password' => Hash::make('password123'),
            'phone' => '085711223344',
            'role' => 'customer',
            'member_tier' => 'gold',
            'loyalty_points' => 420,
            'is_active' => true,
        ]);

        // 2. SEED CATEGORIES
        $categoriesData = [
            ['name' => 'Sembako', 'slug' => 'sembako', 'icon' => 'shopping-bag', 'description' => 'Beras, minyak, gula, tepung, telur, dan kebutuhan pokok harian.'],
            ['name' => 'Minuman', 'slug' => 'minuman', 'icon' => 'coffee', 'description' => 'Air mineral, teh, kopi, susu, dan minuman kemasan segar.'],
            ['name' => 'Bumbu Dapur', 'slug' => 'bumbu-dapur', 'icon' => 'utensils', 'description' => 'Kecap, saus, penyedap rasa, garam, dan rempah siap masak.'],
            ['name' => 'Snack & Camilan', 'slug' => 'snack-camilan', 'icon' => 'cookie', 'description' => 'Biskuit, keripik, cokelat, dan makanan ringan keluarga.'],
            ['name' => 'Alat Mandi & Cuci', 'slug' => 'alat-mandi-cuci', 'icon' => 'sparkles', 'description' => 'Sabun mandi, shampo, deterjen cuci, dan pembersih lantai.'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 3. SEED PRODUCTS
        $productsData = [
            [
                'category_id' => $categories['sembako']->id,
                'name' => 'Beras Premium Pandan Wangi 5kg',
                'slug' => 'beras-premium-pandan-wangi-5kg',
                'barcode' => '899123456001',
                'description' => 'Beras putih pulen pilihan dengan aroma pandan wangi alami tanpa pengawet.',
                'cost_price' => 62000,
                'sell_price' => 72000,
                'stock' => 24,
                'min_stock' => 10,
                'unit' => 'karung',
                'image' => null,
            ],
            [
                'category_id' => $categories['sembako']->id,
                'name' => 'Minyak Goreng Sania Pouch 2L',
                'slug' => 'minyak-goreng-sania-pouch-2l',
                'barcode' => '899123456002',
                'description' => 'Minyak goreng kelapa sawit murni berkualitas tinggi, jernih dan hemat.',
                'cost_price' => 32000,
                'sell_price' => 36500,
                'stock' => 8, // Stok menipis (< min_stock 12)
                'min_stock' => 12,
                'unit' => 'pouch',
                'image' => null,
            ],
            [
                'category_id' => $categories['sembako']->id,
                'name' => 'Gula Pasir Gulaku Kemasan Kuning 1kg',
                'slug' => 'gula-pasir-gulaku-kuning-1kg',
                'barcode' => '899123456003',
                'description' => 'Gula tebu murni manis alami, butiran kristal halus mudah larut.',
                'cost_price' => 13000,
                'sell_price' => 16500,
                'stock' => 45,
                'min_stock' => 15,
                'unit' => 'bungkus',
                'image' => null,
            ],
            [
                'category_id' => $categories['sembako']->id,
                'name' => 'Telur Ayam Negeri Segar 1kg',
                'slug' => 'telur-ayam-negeri-segar-1kg',
                'barcode' => '899123456004',
                'description' => 'Telur ayam negeri segar langsung dari peternakan, isi sekitar 16 butir.',
                'cost_price' => 24500,
                'sell_price' => 28000,
                'stock' => 30,
                'min_stock' => 10,
                'unit' => 'kg',
                'image' => null,
            ],
            [
                'category_id' => $categories['bumbu-dapur']->id,
                'name' => 'Kecap Manis ABC Botol 520ml',
                'slug' => 'kecap-manis-abc-botol-520ml',
                'barcode' => '899123456005',
                'description' => 'Kecap manis kental dari kedelai hitam pilihan untuk cita rasa masakan nusantara.',
                'cost_price' => 15000,
                'sell_price' => 18500,
                'stock' => 18,
                'min_stock' => 8,
                'unit' => 'botol',
                'image' => null,
            ],
            [
                'category_id' => $categories['bumbu-dapur']->id,
                'name' => 'Garam Beryodium Cap Kapal 250g',
                'slug' => 'garam-beryodium-cap-kapal-250g',
                'barcode' => '899123456006',
                'description' => 'Garam dapur beryodium murni dan higienis untuk menjaga kesehatan keluarga.',
                'cost_price' => 2500,
                'sell_price' => 3500,
                'stock' => 50,
                'min_stock' => 20,
                'unit' => 'bungkus',
                'image' => null,
            ],
            [
                'category_id' => $categories['minuman']->id,
                'name' => 'Kopi Kapal Api Special Mix 1 Renceng (10 sachet)',
                'slug' => 'kopi-kapal-api-special-mix-renceng',
                'barcode' => '899123456007',
                'description' => 'Paduan kopi bubuk mantap dengan gula pas, aroma wangi legendaris.',
                'cost_price' => 12500,
                'sell_price' => 15000,
                'stock' => 35,
                'min_stock' => 15,
                'unit' => 'renceng',
                'image' => null,
            ],
            [
                'category_id' => $categories['minuman']->id,
                'name' => 'Teh Pucuk Harum Melati 350ml',
                'slug' => 'teh-pucuk-harum-melati-350ml',
                'barcode' => '899123456008',
                'description' => 'Teh manis segar dari pucuk daun teh pilihan dengan aroma melati.',
                'cost_price' => 2700,
                'sell_price' => 3500,
                'stock' => 48,
                'min_stock' => 24,
                'unit' => 'botol',
                'image' => null,
            ],
            [
                'category_id' => $categories['minuman']->id,
                'name' => 'Air Mineral Le Minerale 600ml',
                'slug' => 'air-mineral-le-minerale-600ml',
                'barcode' => '899123456009',
                'description' => 'Air mineral pegunungan segar dengan mineral alami, kemasan higienis.',
                'cost_price' => 2500,
                'sell_price' => 3500,
                'stock' => 60,
                'min_stock' => 24,
                'unit' => 'botol',
                'image' => null,
            ],
            [
                'category_id' => $categories['snack-camilan']->id,
                'name' => 'Indomie Mi Goreng Spesial 85g',
                'slug' => 'indomie-mi-goreng-spesial-85g',
                'barcode' => '899123456010',
                'description' => 'Mi instan goreng favorit masyarakat Indonesia dengan bumbu gurih nikmat.',
                'cost_price' => 2800,
                'sell_price' => 3500,
                'stock' => 120,
                'min_stock' => 40,
                'unit' => 'bungkus',
                'image' => null,
            ],
            [
                'category_id' => $categories['snack-camilan']->id,
                'name' => 'Biskuit Roma Kelapa 300g',
                'slug' => 'biskuit-roma-kelapa-300g',
                'barcode' => '899123456011',
                'description' => 'Biskuit renyah rasa kelapa asli, teman santai minum teh dan kopi.',
                'cost_price' => 9500,
                'sell_price' => 11500,
                'stock' => 25,
                'min_stock' => 10,
                'unit' => 'bungkus',
                'image' => null,
            ],
            [
                'category_id' => $categories['alat-mandi-cuci']->id,
                'name' => 'Sabun Mandi Lifebuoy Total 10 (85g)',
                'slug' => 'sabun-mandi-lifebuoy-total-10-85g',
                'barcode' => '899123456012',
                'description' => 'Sabun batang antibakteri untuk perlindungan total kuman keluarga.',
                'cost_price' => 3500,
                'sell_price' => 4500,
                'stock' => 40,
                'min_stock' => 15,
                'unit' => 'batang',
                'image' => null,
            ],
            [
                'category_id' => $categories['alat-mandi-cuci']->id,
                'name' => 'Sunlight Pencuci Piring Jeruk Nipis 700ml',
                'slug' => 'sunlight-pencuci-piring-jeruk-nipis-700ml',
                'barcode' => '899123456013',
                'description' => 'Cairan pencuci piring ekstrak jeruk nipis asli, bersihkan lemak 10x lebih cepat.',
                'cost_price' => 12500,
                'sell_price' => 15500,
                'stock' => 20,
                'min_stock' => 8,
                'unit' => 'pouch',
                'image' => null,
            ],
            [
                'category_id' => $categories['alat-mandi-cuci']->id,
                'name' => 'Deterjen Rinso Molto Anti Noda 770g',
                'slug' => 'deterjen-rinso-molto-anti-noda-770g',
                'barcode' => '899123456014',
                'description' => 'Deterjen bubuk dengan keharuman tahan lama dan formula hilangkan noda membandel.',
                'cost_price' => 18500,
                'sell_price' => 22000,
                'stock' => 15,
                'min_stock' => 6,
                'unit' => 'bungkus',
                'image' => null,
            ],
        ];

        $products = [];
        foreach ($productsData as $prod) {
            $products[] = Product::updateOrCreate(['slug' => $prod['slug']], $prod);
        }

        // 4. SEED TOPSIS 6 CRITERIA (Total Weight = 1.0000)
        $criteria = [
            [
                'code' => 'C1',
                'name' => 'Produk Terlaris (Volume Penjualan)',
                'description' => 'Total kuantitas item yang terjual pada periode analisis.',
                'type' => 'benefit',
                'weight' => 0.2500,
                'data_source' => 'total_sold_qty',
            ],
            [
                'code' => 'C2',
                'name' => 'Perputaran Lambat / Dead Stock',
                'description' => 'Jumlah hari sejak penjualan terakhir (semakin lama, semakin dead stock).',
                'type' => 'cost',
                'weight' => 0.1500,
                'data_source' => 'days_without_sales',
            ],
            [
                'code' => 'C3',
                'name' => 'Prioritas Restock',
                'description' => 'Rasio ambang batas stok terhadap sisa stok (Min Stock / Current Stock).',
                'type' => 'benefit',
                'weight' => 0.2000,
                'data_source' => 'restock_urgency',
            ],
            [
                'code' => 'C4',
                'name' => 'Loyalitas Pelanggan',
                'description' => 'Frekuensi pembelian berulang oleh member terdaftar.',
                'type' => 'benefit',
                'weight' => 0.1500,
                'data_source' => 'repeat_buyer_count',
            ],
            [
                'code' => 'C5',
                'name' => 'Margin Keuntungan',
                'description' => 'Persentase selisih harga jual terhadap harga modal.',
                'type' => 'benefit',
                'weight' => 0.1500,
                'data_source' => 'profit_margin_percent',
            ],
            [
                'code' => 'C6',
                'name' => 'Tingkat Retur / Komplain',
                'description' => 'Rasio produk yang mengalami komplain atau pengembalian.',
                'type' => 'cost',
                'weight' => 0.1000,
                'data_source' => 'return_rate',
            ],
        ];

        foreach ($criteria as $crit) {
            TopsisCriterion::updateOrCreate(['code' => $crit['code']], $crit);
        }

        // 5. SEED PROMOS
        Promo::updateOrCreate(['code' => 'SEMBAKOMURAH'], [
            'name' => 'Diskon Sembako Berkah Rp 5.000',
            'type' => 'fixed',
            'value' => 5000,
            'min_purchase' => 50000,
            'max_uses' => 100,
            'used_count' => 8,
            'valid_from' => Carbon::now()->subDays(5),
            'valid_until' => Carbon::now()->addDays(25),
            'is_active' => true,
        ]);

        Promo::updateOrCreate(['code' => 'TOPSIS10'], [
            'name' => 'Promo Pengguna Baru 10%',
            'type' => 'percent',
            'value' => 10,
            'min_purchase' => 30000,
            'max_uses' => 50,
            'used_count' => 14,
            'valid_from' => Carbon::now()->subDays(2),
            'valid_until' => Carbon::now()->addDays(30),
            'is_active' => true,
        ]);

        // 6. SEED SETTINGS
        $settings = [
            ['key' => 'store_name', 'value' => 'Toko Kelontong Berkah (Mobeli)', 'group' => 'store'],
            ['key' => 'store_address', 'value' => 'Jl. Merdeka No. 45, RT 03/RW 02, Sukajadi', 'group' => 'store'],
            ['key' => 'store_phone', 'value' => '081234567890', 'group' => 'store'],
            ['key' => 'store_tagline', 'value' => 'Belanja Sembako Lengkap, Dekat & Terpercaya', 'group' => 'store'],
            ['key' => 'bank_name', 'value' => 'BRI', 'group' => 'payment'],
            ['key' => 'bank_account_number', 'value' => '1234-01-567890-53-1', 'group' => 'payment'],
            ['key' => 'bank_account_name', 'value' => 'Toko Kelontong Berkah', 'group' => 'payment'],
            ['key' => 'wa_gateway', 'value' => 'fonnte', 'group' => 'whatsapp'],
        ];

        foreach ($settings as $st) {
            Setting::updateOrCreate(['key' => $st['key']], $st);
        }

        // 7. INITIAL SHIFT KASIR
        $shift = Shift::create([
            'cashier_id' => $cashier->id,
            'opened_at' => Carbon::now()->subHours(4),
            'opening_cash' => 200000,
            'status' => 'open',
            'notes' => 'Shift pagi berjalan lancar',
        ]);

        // 8. SAMPLE POS TRANSACTIONS (Offline)
        $posOrder = Order::create([
            'order_number' => 'POS-' . date('Ymd') . '-0001',
            'type' => 'offline',
            'cashier_id' => $cashier->id,
            'customer_id' => $customer1->id,
            'shift_id' => $shift->id,
            'subtotal' => 108500,
            'discount' => 3255, // Diskon silver member 3%
            'total' => 105245,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'order_status' => 'completed',
            'paid_at' => Carbon::now()->subHours(2),
        ]);

        OrderItem::create([
            'order_id' => $posOrder->id,
            'product_id' => $products[0]->id, // Beras 5kg
            'product_name' => $products[0]->name,
            'price' => $products[0]->sell_price,
            'cost_price' => $products[0]->cost_price,
            'qty' => 1,
            'subtotal' => 72000,
        ]);

        OrderItem::create([
            'order_id' => $posOrder->id,
            'product_id' => $products[1]->id, // Minyak Sania 2L
            'product_name' => $products[1]->name,
            'price' => $products[1]->sell_price,
            'cost_price' => $products[1]->cost_price,
            'qty' => 1,
            'subtotal' => 36500,
        ]);

        // Sample Online Order
        $onlineOrder = Order::create([
            'order_number' => 'ONL-' . date('Ymd') . '-0001',
            'type' => 'online',
            'customer_id' => $customer2->id,
            'subtotal' => 59500,
            'discount' => 5000,
            'total' => 54500,
            'payment_method' => 'manual_transfer',
            'payment_status' => 'paid',
            'order_status' => 'processing',
            'notes' => 'Tolong antar sore ya',
            'paid_at' => Carbon::now()->subHour(),
        ]);

        OrderItem::create([
            'order_id' => $onlineOrder->id,
            'product_id' => $products[2]->id, // Gula Pasir
            'product_name' => $products[2]->name,
            'price' => 16500,
            'cost_price' => 13000,
            'qty' => 2,
            'subtotal' => 33000,
        ]);

        OrderItem::create([
            'order_id' => $onlineOrder->id,
            'product_id' => $products[3]->id, // Telur Ayam 1kg
            'product_name' => $products[3]->name,
            'price' => 28000,
            'cost_price' => 24500,
            'qty' => 1,
            'subtotal' => 28000,
        ]);
    }
}

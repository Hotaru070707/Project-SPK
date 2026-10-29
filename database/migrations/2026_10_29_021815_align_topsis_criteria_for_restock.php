<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $criteria = [
            'C1' => [
                'name' => 'Tingkat Penjualan',
                'description' => 'Jumlah unit yang terjual selama 30 hari terakhir.',
                'type' => 'benefit',
                'weight' => 0.4000,
                'data_source' => 'order_items.qty',
                'is_active' => true,
            ],
            'C2' => [
                'name' => 'Stok Tersedia',
                'description' => 'Jumlah stok aktual yang tersedia saat perhitungan.',
                'type' => 'cost',
                'weight' => 0.3500,
                'data_source' => 'products.stock',
                'is_active' => true,
            ],
            'C3' => [
                'name' => 'Waktu Pengadaan',
                'description' => 'Waktu tunggu pemasok untuk menyediakan produk dalam hari.',
                'type' => 'cost',
                'weight' => 0.2500,
                'data_source' => 'products.supplier_lead_time_days',
                'is_active' => true,
            ],
        ];

        foreach ($criteria as $code => $attributes) {
            DB::table('topsis_criteria')->where('code', $code)->update($attributes);
        }

        DB::table('topsis_criteria')->where('code', 'C4')->update(['is_active' => false]);
    }

    public function down(): void
    {
        DB::table('topsis_criteria')->where('code', 'C1')->update([
            'name' => 'Urgensi Stok',
            'description' => 'Urgensi dihitung dari stok tersedia dibandingkan stok minimum.',
            'type' => 'benefit',
            'weight' => 0.4000,
            'data_source' => 'stock_urgency',
            'is_active' => true,
        ]);
        DB::table('topsis_criteria')->where('code', 'C2')->update([
            'name' => 'Kecepatan Penjualan',
            'description' => 'Jumlah unit terjual selama 30 hari terakhir.',
            'type' => 'benefit',
            'weight' => 0.3000,
            'data_source' => 'units_sold_30_days',
            'is_active' => true,
        ]);
        DB::table('topsis_criteria')->where('code', 'C3')->update([
            'name' => 'Lama Pengadaan',
            'description' => 'Waktu tunggu pengadaan dari pemasok dalam hari.',
            'type' => 'benefit',
            'weight' => 0.2000,
            'data_source' => 'supplier_lead_time_days',
            'is_active' => true,
        ]);
        DB::table('topsis_criteria')->where('code', 'C4')->update([
            'name' => 'Margin Keuntungan',
            'description' => 'Persentase margin kotor dari harga jual dan harga modal.',
            'type' => 'benefit',
            'weight' => 0.1000,
            'data_source' => 'profit_margin_percent',
            'is_active' => true,
        ]);
    }
};

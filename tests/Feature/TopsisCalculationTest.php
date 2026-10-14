<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\TopsisCriterion;
use App\Models\TopsisResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TopsisCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_calculate_and_review_four_criteria_topsis_ranking(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->createCriteria();
        $category = Category::create([
            'name' => 'Sembako',
            'slug' => 'sembako',
        ]);
        $urgentProduct = $this->createProduct($category, [
            'name' => 'Minyak Goreng',
            'slug' => 'minyak-goreng',
            'stock' => 0,
            'min_stock' => 10,
            'supplier_lead_time_days' => 8,
            'cost_price' => 50,
            'sell_price' => 100,
        ]);
        $this->createProduct($category, [
            'name' => 'Gula Pasir',
            'slug' => 'gula-pasir',
            'stock' => 25,
            'min_stock' => 5,
            'supplier_lead_time_days' => 2,
            'cost_price' => 90,
            'sell_price' => 100,
        ]);

        $order = Order::query()->create([
            'order_number' => 'POS-TEST-0001',
            'type' => 'offline',
            'cashier_id' => $owner->id,
            'subtotal' => 800,
            'discount' => 0,
            'total' => 800,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'order_status' => 'completed',
            'paid_at' => now(),
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $urgentProduct->id,
            'product_name' => $urgentProduct->name,
            'price' => 100,
            'cost_price' => 50,
            'qty' => 8,
            'subtotal' => 800,
        ]);

        $response = $this->actingAs($owner)->get(route('admin.topsis.calculate'));

        $response->assertOk()
            ->assertSee('Minyak Goreng')
            ->assertSee('Matriks Keputusan (X)');

        $result = TopsisResult::query()->latest()->firstOrFail();
        $this->assertCount(4, $result->criteria_snapshot);
        $rankedProducts = array_values($result->preferences);
        $this->assertSame('Minyak Goreng', $rankedProducts[0]['product']['name']);
        $this->assertSame('Restock Segera', $rankedProducts[0]['recommendation']);
        $this->assertStringContainsString('terjual 8 unit/30 hari', $rankedProducts[0]['reason']);
    }

    public function test_calculation_is_blocked_if_a_product_has_no_supplier_lead_time(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->createCriteria();
        $category = Category::create([
            'name' => 'Minuman',
            'slug' => 'minuman',
        ]);
        $this->createProduct($category, [
            'name' => 'Air Mineral',
            'slug' => 'air-mineral',
            'supplier_lead_time_days' => null,
        ]);
        $this->createProduct($category, [
            'name' => 'Teh',
            'slug' => 'teh',
            'supplier_lead_time_days' => 2,
        ]);

        $this->actingAs($owner)
            ->get(route('admin.topsis.calculate'))
            ->assertRedirect(route('admin.products'))
            ->assertSessionHas('error', 'Lengkapi waktu pengadaan pemasok untuk produk berikut sebelum menghitung TOPSIS: Air Mineral');

        $this->assertDatabaseCount('topsis_results', 0);
    }

    public function test_weight_update_rejects_values_that_do_not_sum_to_one(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'is_active' => true,
        ]);
        $criteria = $this->createCriteria();
        $weights = $criteria->mapWithKeys(fn (TopsisCriterion $criterion): array => [
            $criterion->id => 0.1,
        ])->all();

        $this->actingAs($owner)
            ->post(route('admin.topsis.criteria.update'), ['weights' => $weights])
            ->assertSessionHasErrors('weights');

        $this->assertEquals(1.0, (float) TopsisCriterion::query()->sum('weight'));
    }

    public function test_database_seeder_creates_demo_products_and_four_topsis_criteria(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', ['email' => 'owner@tokotopsis.id']);
        $this->assertDatabaseHas('users', ['email' => 'kasir@tokotopsis.id']);
        $this->assertDatabaseCount('products', 14);
        $this->assertDatabaseCount('topsis_criteria', 4);
        $this->assertDatabaseHas('products', [
            'slug' => 'beras-premium-pandan-wangi-5kg',
            'supplier_lead_time_days' => 3,
        ]);
    }

    private function createCriteria(): Collection
    {
        return collect([
            ['code' => 'C1', 'name' => 'Urgensi Stok', 'weight' => 0.4],
            ['code' => 'C2', 'name' => 'Kecepatan Penjualan', 'weight' => 0.3],
            ['code' => 'C3', 'name' => 'Lama Pengadaan', 'weight' => 0.2],
            ['code' => 'C4', 'name' => 'Margin Keuntungan', 'weight' => 0.1],
        ])->map(fn (array $criterion): TopsisCriterion => TopsisCriterion::query()->create([
            ...$criterion,
            'type' => 'benefit',
            'is_active' => true,
        ]));
    }

    private function createProduct(Category $category, array $attributes): Product
    {
        return Product::query()->create([
            'category_id' => $category->id,
            'barcode' => null,
            'description' => null,
            'cost_price' => 10,
            'sell_price' => 20,
            'stock' => 10,
            'min_stock' => 2,
            'supplier_lead_time_days' => 3,
            'unit' => 'pcs',
            'is_active' => true,
            ...$attributes,
        ]);
    }
}

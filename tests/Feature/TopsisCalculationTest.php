<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\TopsisCriterion;
use App\Models\TopsisResult;
use App\Models\User;
use App\Services\TopsisCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TopsisCalculationTest extends TestCase
{
    use RefreshDatabase;

    private int $orderCounter = 0;

    public function test_owner_can_run_three_criteria_topsis_with_the_expected_snapshot(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 29)->setTime(12, 0));
        $owner = $this->createOwner();
        $this->createCriteria();
        $products = $this->createAlternativeProducts();

        foreach (array_values($products) as $index => $product) {
            $this->recordSale($owner, $product, $index + 1);
        }

        $this->actingAs($owner)
            ->post(route('admin.topsis.calculate.run'))
            ->assertRedirect(route('admin.topsis.calculate'))
            ->assertSessionHas('success');

        $result = TopsisResult::query()->latest('id')->firstOrFail();
        $this->assertSame(['C1', 'C2', 'C3'], array_column($result->criteria_snapshot['criteria'], 'code'));
        $this->assertSame(
            [0.4, 0.35, 0.25],
            array_column($result->criteria_snapshot['criteria'], 'weight')
        );
        $this->assertEquals(1.0, $result->criteria_snapshot['weight_total']);
        $this->assertEquals(1.0, $result->decision_matrix[$products['Beras']->id]['C1']);
        $this->assertEquals(1.0, $result->decision_matrix[$products['Beras']->id]['C2']);
        $this->assertEquals(1.0, $result->decision_matrix[$products['Beras']->id]['C3']);

        $this->assertEquals(
            1 / sqrt(30),
            $result->normalized_matrix[$products['Beras']->id]['C1'],
            '',
            0.0000001
        );
        $this->assertEquals(
            0.4 / sqrt(30),
            $result->weighted_matrix[$products['Beras']->id]['C1'],
            '',
            0.0000001
        );
        $this->assertEquals(
            0.35 / sqrt(30),
            $result->ideal_positive['C2'],
            '',
            0.0000001
        );
        $this->assertEquals(
            1.4 / sqrt(30),
            $result->ideal_negative['C2'],
            '',
            0.0000001
        );

        $expectedPreferences = [];
        foreach (array_values($products) as $index => $product) {
            $value = $index + 1;
            $dPlus = sqrt(
                (0.4 * (4 - $value)) ** 2
                + (0.35 * ($value - 1)) ** 2
                + (0.25 * ($value - 1)) ** 2
            );
            $dMinus = sqrt(
                (0.4 * ($value - 1)) ** 2
                + (0.35 * (4 - $value)) ** 2
                + (0.25 * (4 - $value)) ** 2
            );
            $expectedPreferences[$product->id] = $dMinus / ($dPlus + $dMinus);
        }
        arsort($expectedPreferences);
        $actualPreferences = $result->preferences;
        $this->assertSame(array_keys($expectedPreferences), array_map('intval', array_keys($actualPreferences)));
        $highestRanked = reset($actualPreferences);
        $this->assertSame('Beras Premium 5kg', $highestRanked['product']['name']);
        $this->assertSame(1, $highestRanked['rank']);
        $this->assertEqualsWithDelta(
            reset($expectedPreferences),
            $highestRanked['ci'],
            0.0000001
        );
        $this->assertEqualsWithDelta(
            0.4 * 3 / sqrt(30),
            $highestRanked['d_plus'],
            0.0000001
        );
        $this->assertEqualsWithDelta(
            sqrt((0.35 * 3 / sqrt(30)) ** 2 + (0.25 * 3 / sqrt(30)) ** 2),
            $highestRanked['d_minus'],
            0.0000001
        );

        $this->actingAs($owner)
            ->get(route('admin.topsis.calculate'))
            ->assertOk()
            ->assertSee('Matriks keputusan awal')
            ->assertSee('Nilai preferensi')
            ->assertSee('29/09/2026 12:00–29/10/2026 12:00');

        $this->actingAs($owner)
            ->get(route('admin.topsis.recommendations'))
            ->assertOk()
            ->assertSee('Jumlah terjual')
            ->assertSee('prioritas relatif', false)
            ->assertSee('bukan kepastian stok akan habis');
    }

    public function test_sales_count_only_paid_normal_units_and_excludes_cancelled_or_expired_sales(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 29)->setTime(12, 0));
        $owner = $this->createOwner();
        $this->createCriteria();
        $products = $this->createAlternativeProducts();
        $beras = $products['Beras'];

        $this->recordSale($owner, $beras, 8);
        $this->recordSale($owner, $beras, 50, ['payment_status' => 'unpaid']);
        $this->recordSale($owner, $beras, 40, ['order_status' => 'cancelled']);
        $this->recordSale($owner, $beras, 30, ['paid_at' => now()->subDays(31)]);
        $this->recordSale($owner, $beras, 20, [], 'returned');

        $this->actingAs($owner)->post(route('admin.topsis.calculate.run'));

        $result = TopsisResult::query()->latest('id')->firstOrFail();
        $this->assertEquals(8.0, $result->decision_matrix[$beras->id]['C1']);
    }

    public function test_calculation_is_blocked_when_supplier_lead_time_is_missing(): void
    {
        $owner = $this->createOwner();
        $this->createCriteria();
        $this->createAlternativeProducts(['Kecap Manis' => null]);

        $this->actingAs($owner)
            ->post(route('admin.topsis.calculate.run'))
            ->assertRedirect(route('admin.topsis.criteria'))
            ->assertSessionHas('error', 'Lengkapi waktu pengadaan supplier untuk: Kecap Manis ABC 520ml.');

        $this->assertDatabaseCount('topsis_results', 0);
    }

    public function test_calculation_is_blocked_when_a_required_alternative_is_missing(): void
    {
        $owner = $this->createOwner();
        $this->createCriteria();
        $category = $this->createCategory();
        foreach (['Beras Premium 5kg', 'Minyak Goreng 2L', 'Telur Ayam 1kg'] as $name) {
            $this->createProduct($category, $name, 4, 2);
        }

        $this->actingAs($owner)
            ->post(route('admin.topsis.calculate.run'))
            ->assertRedirect(route('admin.topsis.criteria'))
            ->assertSessionHas('error', 'Produk alternatif belum tersedia atau tidak aktif: Kecap Manis.');

        $this->assertDatabaseCount('topsis_results', 0);
    }

    public function test_negative_weights_and_weights_that_do_not_sum_to_one_are_rejected(): void
    {
        $owner = $this->createOwner();
        $criteria = $this->createCriteria();
        $validWeights = $criteria->mapWithKeys(fn (TopsisCriterion $criterion): array => [
            $criterion->id => (float) $criterion->weight,
        ])->all();

        $negativeWeights = $validWeights;
        $negativeWeights[$criteria[0]->id] = -0.1;
        $this->actingAs($owner)
            ->post(route('admin.topsis.criteria.update'), ['weights' => $negativeWeights])
            ->assertSessionHasErrors("weights.{$criteria[0]->id}");

        $invalidTotalWeights = [
            $criteria[0]->id => 0.4,
            $criteria[1]->id => 0.3,
            $criteria[2]->id => 0.2,
        ];
        $this->post(route('admin.topsis.criteria.update'), ['weights' => $invalidTotalWeights])
            ->assertSessionHasErrors('weights');

        $emptyWeights = $validWeights;
        $emptyWeights[$criteria[1]->id] = '';
        $this->post(route('admin.topsis.criteria.update'), ['weights' => $emptyWeights])
            ->assertSessionHasErrors("weights.{$criteria[1]->id}");

        $nonNumericWeights = $validWeights;
        $nonNumericWeights[$criteria[1]->id] = 'bobot';
        $this->post(route('admin.topsis.criteria.update'), ['weights' => $nonNumericWeights])
            ->assertSessionHasErrors("weights.{$criteria[1]->id}");

        $this->assertEquals(1.0, (float) TopsisCriterion::query()->sum('weight'));
        $this->assertSame(
            [0.4, 0.35, 0.25],
            TopsisCriterion::query()->orderBy('code')->pluck('weight')->map(fn ($weight): float => (float) $weight)->all()
        );
    }

    public function test_default_weights_are_exact_and_frontend_has_live_validation_hooks(): void
    {
        $owner = $this->createOwner();
        $criteria = $this->createCriteria();

        $this->assertSame(
            [0.4, 0.35, 0.25],
            $criteria->pluck('weight')->map(fn ($weight): float => (float) $weight)->all()
        );

        $this->actingAs($owner)
            ->get(route('admin.topsis.criteria'))
            ->assertOk()
            ->assertSee('data-topsis-weights', false)
            ->assertSee('data-weight-input', false)
            ->assertSee('data-weight-edit-toggle', false)
            ->assertSee('data-weight-save', false)
            ->assertSee('data-editing="false"', false)
            ->assertSee('type="number" step="0.0001" min="0" max="1"', false)
            ->assertSee('id="topsis-weight-total"', false)
            ->assertSee('id="topsis-weight-status"', false)
            ->assertSee('id="topsis-weight-error"', false)
            ->assertSee('Edit Bobot')
            ->assertSee('Simpan Perubahan Bobot')
            ->assertSee('Tingkat Penjualan');
    }

    public function test_cost_and_benefit_types_change_the_ideal_solutions_in_topsis_service(): void
    {
        $service = app(TopsisCalculationService::class);
        $criteria = [
            ['code' => 'C1', 'name' => 'Penjualan', 'type' => 'benefit', 'weight' => 0.4],
            ['code' => 'C2', 'name' => 'Stok', 'type' => 'cost', 'weight' => 0.35],
            ['code' => 'C3', 'name' => 'Lead time', 'type' => 'cost', 'weight' => 0.25],
        ];
        $matrix = [
            1 => ['product' => ['id' => 1, 'name' => 'A'], 'C1' => 1, 'C2' => 2, 'C3' => 3],
            2 => ['product' => ['id' => 2, 'name' => 'B'], 'C1' => 2, 'C2' => 4, 'C3' => 1],
        ];

        $costResult = $service->calculate($criteria, $matrix);
        $criteria[1]['type'] = 'benefit';
        $benefitResult = $service->calculate($criteria, $matrix);

        $this->assertEquals(
            min(array_column($costResult['weighted_matrix'], 'C2')),
            $costResult['ideal_positive']['C2']
        );
        $this->assertEquals(
            max(array_column($benefitResult['weighted_matrix'], 'C2')),
            $benefitResult['ideal_positive']['C2']
        );
        $this->assertNotEquals($costResult['ideal_positive']['C2'], $benefitResult['ideal_positive']['C2']);
    }

    public function test_topsis_service_rejects_empty_alternatives(): void
    {
        $service = app(TopsisCalculationService::class);
        $criteria = [
            ['code' => 'C1', 'name' => 'Penjualan', 'type' => 'benefit', 'weight' => 0.4],
            ['code' => 'C2', 'name' => 'Stok', 'type' => 'cost', 'weight' => 0.35],
            ['code' => 'C3', 'name' => 'Lead time', 'type' => 'cost', 'weight' => 0.25],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $service->calculate($criteria, []);
    }

    public function test_topsis_service_rejects_a_total_weight_of_ninety_percent(): void
    {
        $service = app(TopsisCalculationService::class);
        $criteria = [
            ['code' => 'C1', 'name' => 'Penjualan', 'type' => 'benefit', 'weight' => 0.4],
            ['code' => 'C2', 'name' => 'Stok', 'type' => 'cost', 'weight' => 0.3],
            ['code' => 'C3', 'name' => 'Lead time', 'type' => 'cost', 'weight' => 0.2],
        ];
        $matrix = [
            1 => ['product' => ['id' => 1, 'name' => 'A'], 'C1' => 1, 'C2' => 2, 'C3' => 3],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $service->calculate($criteria, $matrix);
    }

    public function test_topsis_service_rejects_missing_or_negative_metric_values(): void
    {
        $service = app(TopsisCalculationService::class);
        $criteria = [
            ['code' => 'C1', 'name' => 'Penjualan', 'type' => 'benefit', 'weight' => 0.4],
            ['code' => 'C2', 'name' => 'Stok', 'type' => 'cost', 'weight' => 0.35],
            ['code' => 'C3', 'name' => 'Lead time', 'type' => 'cost', 'weight' => 0.25],
        ];
        $matrix = [
            1 => ['product' => ['id' => 1, 'name' => 'A'], 'C1' => 1, 'C2' => 2],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $service->calculate($criteria, $matrix);
    }

    public function test_owner_criteria_screen_shows_business_labels_and_disables_incomplete_data(): void
    {
        $owner = $this->createOwner();
        $this->createCriteria();
        $this->createAlternativeProducts(['Telur Ayam' => null]);

        $this->actingAs($owner)
            ->get(route('admin.topsis.criteria'))
            ->assertOk()
            ->assertSee('Pengaturan Kriteria')
            ->assertSee('Mengukur jumlah unit yang terjual selama periode penilaian')
            ->assertSee('products.stock')
            ->assertSee('Benefit')
            ->assertSee('Cost')
            ->assertSee('Waktu pengadaan supplier belum diisi')
            ->assertSee('disabled', false);
    }

    public function test_owner_can_save_valid_custom_weights(): void
    {
        $owner = $this->createOwner();
        $criteria = $this->createCriteria();
        $this->createAlternativeProducts();
        $weights = [
            $criteria[0]->id => 0.5,
            $criteria[1]->id => 0.3,
            $criteria[2]->id => 0.2,
        ];

        $this->actingAs($owner)
            ->followingRedirects()
            ->post(route('admin.topsis.criteria.update'), ['weights' => $weights])
            ->assertOk()
            ->assertSee('Bobot kriteria TOPSIS berhasil diperbarui.');

        $this->assertSame(
            [0.5, 0.3, 0.2],
            TopsisCriterion::query()->orderBy('code')->pluck('weight')->map(fn ($weight): float => (float) $weight)->all()
        );

        $this->post(route('admin.topsis.calculate.run'))
            ->assertRedirect(route('admin.topsis.calculate'));

        $result = TopsisResult::query()->latest('id')->firstOrFail();
        $this->assertSame([0.5, 0.3, 0.2], array_column($result->criteria_snapshot['criteria'], 'weight'));
    }

    public function test_calculation_is_blocked_if_saved_weights_are_invalid(): void
    {
        $owner = $this->createOwner();
        $criteria = $this->createCriteria();
        $criteria[0]->update(['weight' => 0.4]);
        $criteria[1]->update(['weight' => 0.3]);
        $criteria[2]->update(['weight' => 0.2]);
        $this->createAlternativeProducts();

        $this->actingAs($owner)
            ->post(route('admin.topsis.calculate.run'))
            ->assertRedirect(route('admin.topsis.criteria'))
            ->assertSessionHas('error', 'Total bobot kriteria harus tepat 100% sebelum perhitungan.');

        $this->assertDatabaseCount('topsis_results', 0);
    }

    public function test_calculation_is_blocked_when_active_criterion_source_or_type_is_inconsistent(): void
    {
        $owner = $this->createOwner();
        $criteria = $this->createCriteria();
        $criteria[2]->update(['data_source' => 'supplier_lead_time_days']);
        $this->createAlternativeProducts();

        $this->actingAs($owner)
            ->post(route('admin.topsis.calculate.run'))
            ->assertRedirect(route('admin.topsis.criteria'))
            ->assertSessionHas('error', 'Konfigurasi kriteria TOPSIS tidak sesuai dengan sumber, nama, atau tipe yang diwajibkan.');

        $this->assertDatabaseCount('topsis_results', 0);
    }

    public function test_zero_divisor_and_identical_alternatives_are_handled_without_invalid_numbers(): void
    {
        $owner = $this->createOwner();
        $this->createCriteria();
        $products = $this->createAlternativeProducts(
            stocks: ['Beras' => 5, 'Minyak Goreng' => 5, 'Telur Ayam' => 5, 'Kecap Manis' => 5],
            leadTimes: ['Beras' => 2, 'Minyak Goreng' => 2, 'Telur Ayam' => 2, 'Kecap Manis' => 2]
        );

        $this->actingAs($owner)
            ->post(route('admin.topsis.calculate.run'))
            ->assertRedirect(route('admin.topsis.calculate'));

        $result = TopsisResult::query()->latest('id')->firstOrFail();
        $this->assertSame(['C1'], $result->criteria_snapshot['zero_divisor_codes']);
        $this->assertTrue($result->criteria_snapshot['alternatives_identical']);

        foreach ($result->normalized_matrix as $row) {
            $this->assertEquals(0.0, $row['C1']);
        }
        foreach ($result->preferences as $preference) {
            $this->assertEquals(0.5, $preference['ci']);
        }

        $this->actingAs($owner)
            ->get(route('admin.topsis.calculate'))
            ->assertOk()
            ->assertSee('Penyebut normalisasi bernilai nol')
            ->assertSee('Semua alternatif memiliki nilai kriteria identik');
    }

    public function test_history_and_recommendations_use_the_saved_snapshot_instead_of_current_product_data(): void
    {
        $owner = $this->createOwner();
        $this->createCriteria();
        $products = $this->createAlternativeProducts();
        $beras = $products['Beras'];

        $this->actingAs($owner)->post(route('admin.topsis.calculate.run'));
        $result = TopsisResult::query()->latest('id')->firstOrFail();
        $savedStock = $result->decision_matrix[$beras->id]['C2'];
        $beras->update(['stock' => 999, 'supplier_lead_time_days' => 99]);

        $this->actingAs($owner)
            ->get(route('admin.topsis.history'))
            ->assertOk()
            ->assertSee('Riwayat Perhitungan');

        $this->actingAs($owner)
            ->get(route('admin.topsis.history.show', $result))
            ->assertOk()
            ->assertSee('Beras Premium 5kg')
            ->assertDontSee('999,0000');

        $this->actingAs($owner)
            ->get(route('admin.topsis.recommendations'))
            ->assertOk()
            ->assertDontSee('999 pcs')
            ->assertDontSee('99 hari');

        $this->assertSame(1.0, (float) $savedStock);
        $this->assertSame(999, $beras->fresh()->stock);
    }

    public function test_topsis_routes_remain_restricted_to_authenticated_owners(): void
    {
        $this->get(route('admin.topsis.history'))
            ->assertRedirect(route('login'));
        $this->post(route('admin.topsis.calculate.run'))
            ->assertRedirect(route('login'));

        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $this->actingAs($cashier)
            ->get(route('admin.topsis.criteria'))
            ->assertForbidden();
    }

    public function test_database_seeder_creates_three_topsis_criteria_without_fabricated_lead_times(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', ['email' => 'owner@tokotopsis.id']);
        $this->assertDatabaseHas('users', ['email' => 'kasir@tokotopsis.id']);
        $this->assertDatabaseCount('products', 14);
        $this->assertDatabaseCount('topsis_criteria', 3);
        $this->assertDatabaseHas('topsis_criteria', [
            'code' => 'C1',
            'name' => 'Tingkat Penjualan',
            'type' => 'benefit',
            'weight' => 0.4000,
            'data_source' => 'order_items.qty',
        ]);
        $this->assertDatabaseHas('topsis_criteria', [
            'code' => 'C2',
            'name' => 'Stok Tersedia',
            'type' => 'cost',
            'weight' => 0.3500,
        ]);
        $this->assertDatabaseHas('topsis_criteria', [
            'code' => 'C3',
            'name' => 'Waktu Pengadaan',
            'type' => 'cost',
            'weight' => 0.2500,
            'data_source' => 'products.supplier_lead_time_days',
        ]);
        $this->assertDatabaseHas('products', [
            'slug' => 'beras-premium-pandan-wangi-5kg',
            'supplier_lead_time_days' => null,
        ]);
    }

    private function createOwner(): User
    {
        return User::factory()->create([
            'role' => 'owner',
            'is_active' => true,
        ]);
    }

    private function createCriteria(): Collection
    {
        return collect([
            ['code' => 'C1', 'name' => 'Tingkat Penjualan', 'type' => 'benefit', 'weight' => 0.4, 'data_source' => 'order_items.qty'],
            ['code' => 'C2', 'name' => 'Stok Tersedia', 'type' => 'cost', 'weight' => 0.35, 'data_source' => 'products.stock'],
            ['code' => 'C3', 'name' => 'Waktu Pengadaan', 'type' => 'cost', 'weight' => 0.25, 'data_source' => 'products.supplier_lead_time_days'],
        ])->map(fn (array $criterion): TopsisCriterion => TopsisCriterion::query()->create([
            ...$criterion,
            'is_active' => true,
        ]));
    }

    /**
     * @param  array<string, int|null>  $leadTimes
     * @param  array<string, int>  $stocks
     * @return array<string, Product>
     */
    private function createAlternativeProducts(array $leadTimes = [], array $stocks = []): array
    {
        $category = $this->createCategory();
        $alternativeData = [
            'Beras' => ['Beras Premium 5kg', 1],
            'Minyak Goreng' => ['Minyak Goreng 2L', 2],
            'Telur Ayam' => ['Telur Ayam 1kg', 3],
            'Kecap Manis' => ['Kecap Manis ABC 520ml', 4],
        ];
        $products = [];

        foreach ($alternativeData as $key => [$name, $value]) {
            $products[$key] = $this->createProduct(
                $category,
                $name,
                $stocks[$key] ?? $value,
                array_key_exists($key, $leadTimes) ? $leadTimes[$key] : $value
            );
        }

        return $products;
    }

    private function createCategory(): Category
    {
        return Category::query()->create([
            'name' => 'Sembako',
            'slug' => 'sembako-'.$this->orderCounter,
        ]);
    }

    private function createProduct(Category $category, string $name, int $stock, ?int $leadTime): Product
    {
        $slug = str()->slug($name);

        return Product::query()->create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'barcode' => null,
            'description' => null,
            'cost_price' => 10,
            'sell_price' => 20,
            'stock' => $stock,
            'min_stock' => 2,
            'supplier_lead_time_days' => $leadTime,
            'unit' => 'pcs',
            'is_active' => true,
        ]);
    }

    private function recordSale(
        User $owner,
        Product $product,
        int $qty,
        array $orderOverrides = [],
        string $itemStatus = 'normal'
    ): void {
        $order = Order::query()->create([
            'order_number' => 'POS-TOPSIS-'.str_pad((string) ++$this->orderCounter, 4, '0', STR_PAD_LEFT),
            'type' => 'offline',
            'cashier_id' => $owner->id,
            'subtotal' => $qty * 20,
            'discount' => 0,
            'total' => $qty * 20,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'order_status' => 'completed',
            'paid_at' => now(),
            ...$orderOverrides,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 20,
            'cost_price' => 10,
            'qty' => $qty,
            'subtotal' => $qty * 20,
            'item_status' => $itemStatus,
        ]);
    }
}

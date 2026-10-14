<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CashierPosTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_process_a_valid_transaction_and_reduce_stock(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Sembako',
            'slug' => 'sembako',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Premium',
            'slug' => 'beras-premium',
            'barcode' => 'BR001',
            'cost_price' => 12500,
            'sell_price' => 15000,
            'stock' => 5,
            'min_stock' => 2,
            'unit' => 'kg',
            'is_active' => true,
        ]);

        $this->actingAs($cashier);
        $this->get(route('cashier.pos'))->assertOk();

        $idempotencyKey = (string) Str::uuid();
        $payload = [
            'items' => [[
                'id' => $product->id,
                'name' => 'Harga palsu',
                'price' => 1,
                'cost_price' => 0,
                'qty' => 2,
            ]],
            'payment_method' => 'cash',
            'subtotal' => 30000,
            'discount' => 0,
            'total' => 30000,
            'cash_given' => 35000,
            'idempotency_key' => $idempotencyKey,
        ];

        $response = $this->postJson(route('cashier.store'), $payload);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('total', 30000);
        $response->assertJsonPath('cash_given', 35000);
        $response->assertJsonPath('change', 5000);
        $this->assertNotSame($idempotencyKey, $response->json('next_idempotency_key'));
        $this->assertDatabaseHas('orders', [
            'cashier_id' => $cashier->id,
            'idempotency_key' => $idempotencyKey,
            'cash_given' => 35000,
            'change_amount' => 5000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_name' => 'Beras Premium',
            'price' => 15000,
            'cost_price' => 12500,
            'subtotal' => 30000,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'qty' => 2,
        ]);
        $this->assertSame(3, $product->fresh()->stock);

        $retryResponse = $this->postJson(route('cashier.store'), [
            'idempotency_key' => $idempotencyKey,
        ]);

        $retryResponse->assertOk();
        $retryResponse->assertJsonPath('success', true);
        $retryResponse->assertJsonPath('order_number', $response->json('order_number'));
        $retryResponse->assertJsonPath('cash_given', 35000);
        $retryResponse->assertJsonPath('change', 5000);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_cashier_cannot_sell_more_than_available_stock(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Minyak & Bumbu',
            'slug' => 'minyak-bumbu',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Minyak Goreng',
            'slug' => 'minyak-goreng',
            'barcode' => 'MG002',
            'cost_price' => 18000,
            'sell_price' => 22000,
            'stock' => 2,
            'min_stock' => 1,
            'unit' => 'liter',
            'is_active' => true,
        ]);

        $this->actingAs($cashier);

        $response = $this->postJson(route('cashier.store'), [
            'items' => [[
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->sell_price,
                'cost_price' => $product->cost_price,
                'qty' => 3,
            ]],
            'payment_method' => 'cash',
            'subtotal' => 66000,
            'discount' => 0,
            'total' => 66000,
            'cash_given' => 70000,
            'idempotency_key' => (string) Str::uuid(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cashier_cannot_access_admin_pages(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->actingAs($cashier)
            ->get('/admin')
            ->assertForbidden();
    }
}

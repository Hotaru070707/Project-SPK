<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_record_stock_in_and_the_movement_is_audited(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $product = $this->createProduct(stock: 5);

        $this->actingAs($owner)
            ->post(route('admin.stock.store'), [
                'product_id' => $product->id,
                'type' => 'in',
                'qty' => 3,
                'reason' => 'Pembelian dari pemasok',
            ])
            ->assertRedirect(route('admin.stock'));

        $this->assertSame(8, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'type' => 'in',
            'qty' => 3,
            'stock_before' => 5,
            'stock_after' => 8,
            'reason' => 'Pembelian dari pemasok',
        ]);
    }

    public function test_stock_cannot_be_adjusted_below_zero(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $product = $this->createProduct(stock: 2);

        $this->actingAs($owner)
            ->post(route('admin.stock.store'), [
                'product_id' => $product->id,
                'type' => 'out',
                'qty' => 3,
                'reason' => 'Pengeluaran stok',
            ])
            ->assertSessionHasErrors('qty');

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_sales_report_separates_revenue_from_gross_profit(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $product = $this->createProduct(stock: 5);
        $order = Order::query()->create([
            'order_number' => 'POS-REPORT-0001',
            'type' => 'offline',
            'cashier_id' => $owner->id,
            'subtotal' => 200,
            'discount' => 0,
            'total' => 200,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'order_status' => 'completed',
            'paid_at' => now(),
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 100,
            'cost_price' => 60,
            'qty' => 2,
            'subtotal' => 200,
        ]);

        $this->actingAs($owner)
            ->get(route('admin.reports'))
            ->assertOk()
            ->assertSee('Omzet Transaksi Lunas')
            ->assertSee('Laba Kotor')
            ->assertSee('Rp 200')
            ->assertSee('Rp 80')
            ->assertSee($product->name);
    }

    public function test_owner_can_create_cashier_account_without_assigning_another_role(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);

        $this->actingAs($owner)
            ->post(route('admin.users.store'), [
                'name' => 'Kasir Baru',
                'email' => 'kasir-baru@example.test',
                'password' => 'strong-password',
                'role' => 'owner',
            ])
            ->assertRedirect(route('admin.users'));

        $cashier = User::query()->where('email', 'kasir-baru@example.test')->firstOrFail();
        $this->assertSame('cashier', $cashier->role);
        $this->assertTrue(Hash::check('strong-password', $cashier->password));
    }

    public function test_owner_is_redirected_from_storefront_and_online_checkout_routes(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);

        $this->actingAs($owner)
            ->get(route('home'))
            ->assertRedirect(route('admin.dashboard'));

        $this->get(route('catalog'))
            ->assertRedirect(route('admin.dashboard'));

        $this->get(route('cart.checkout'))
            ->assertRedirect(route('admin.dashboard'));

        $this->post(route('cart.checkout.process'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_owner_storefront_layout_does_not_show_catalog_or_cart_links(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);

        $this->actingAs($owner)
            ->get(route('about'))
            ->assertOk()
            ->assertDontSee(route('catalog'), false)
            ->assertDontSee(route('cart.index'), false)
            ->assertSee(route('admin.dashboard'), false);
    }

    public function test_customers_can_still_open_the_catalog(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($customer)
            ->get(route('catalog'))
            ->assertOk();
    }

    private function createProduct(int $stock): Product
    {
        $category = Category::query()->create([
            'name' => 'Sembako',
            'slug' => 'sembako',
        ]);

        return Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Beras Premium',
            'slug' => 'beras-premium',
            'cost_price' => 60,
            'sell_price' => 100,
            'stock' => $stock,
            'min_stock' => 3,
            'supplier_lead_time_days' => 2,
            'unit' => 'kg',
            'is_active' => true,
        ]);
    }
}

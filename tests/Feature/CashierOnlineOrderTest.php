<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CashierOnlineOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_view_the_selected_online_order_details(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'Pelanggan Contoh',
            'phone' => '081234567890',
            'is_active' => true,
        ]);
        $order = $this->createOnlineOrder($customer, 'ONL-DETAIL-001', 'paid', 'processing');
        $product = $this->createProduct();
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Nama Snapshot Produk',
            'price' => 12500,
            'cost_price' => 9000,
            'qty' => 2,
            'subtotal' => 25000,
        ]);
        $product->update(['sell_price' => 50000]);

        $response = $this->actingAs($cashier)->get(route('cashier.orders.show', $order->order_number));

        $response->assertOk()
            ->assertSee('ONL-DETAIL-001')
            ->assertSee('Pelanggan Contoh')
            ->assertSee('081234567890')
            ->assertSee('Nama Snapshot Produk')
            ->assertSee('Rp 12.500')
            ->assertSee('Rp 25.000')
            ->assertDontSee('Rp 50.000')
            ->assertSee('Siap Diambil', false)
            ->assertSee('Tandai Siap Diambil');
        $this->assertSame('processing', $order->fresh()->order_status);
    }

    public function test_cashier_can_mark_a_paid_processing_order_ready_without_changing_payment_or_stock(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $order = $this->createOnlineOrder($customer, 'ONL-READY-001', 'paid', 'processing');
        $product = $this->createProduct();
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 12500,
            'cost_price' => 9000,
            'qty' => 2,
            'subtotal' => 25000,
        ]);

        $response = $this->actingAs($cashier)->post(route('cashier.orders.ready', $order->order_number));

        $response->assertRedirect(route('cashier.orders.show', $order->order_number))
            ->assertSessionHas('success', 'Pesanan berhasil ditandai siap diambil.');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'order_status' => 'packed',
        ]);
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_cashier_can_complete_a_paid_ready_order_and_cannot_complete_it_again(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $order = $this->createOnlineOrder($customer, 'ONL-COMPLETE-001', 'paid', 'packed');

        $response = $this->actingAs($cashier)->post(route('cashier.orders.complete', $order->order_number));

        $response->assertRedirect(route('cashier.orders.show', $order->order_number))
            ->assertSessionHas('success', 'Penyerahan berhasil dikonfirmasi dan pesanan telah selesai.');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'order_status' => 'completed',
            'cashier_id' => $cashier->id,
        ]);
        $this->get(route('cashier.orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('Diselesaikan pada')
            ->assertSee('Petugas penyelesaian')
            ->assertSee($cashier->name);

        $retryResponse = $this->post(route('cashier.orders.complete', $order->order_number));

        $retryResponse->assertRedirect(route('cashier.orders.show', $order->order_number))
            ->assertSessionHas('error', 'Status pesanan sudah berubah atau tidak sesuai untuk tindakan ini.');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'completed',
            'cashier_id' => $cashier->id,
        ]);
    }

    public function test_cashier_cannot_advance_unpaid_or_ineligible_orders(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $unpaidOrder = $this->createOnlineOrder($customer, 'ONL-UNPAID-001', 'unpaid', 'processing');
        $cancelledOrder = $this->createOnlineOrder($customer, 'ONL-CANCELLED-001', 'paid', 'cancelled');

        $this->actingAs($cashier)
            ->post(route('cashier.orders.ready', $unpaidOrder->order_number))
            ->assertRedirect(route('cashier.orders.show', $unpaidOrder->order_number))
            ->assertSessionHas('error', 'Pembayaran belum dikonfirmasi. Pesanan tidak dapat diproses.');

        $this->post(route('cashier.orders.ready', $cancelledOrder->order_number))
            ->assertRedirect(route('cashier.orders.show', $cancelledOrder->order_number))
            ->assertSessionHas('error', 'Status pesanan sudah berubah atau tidak sesuai untuk tindakan ini.');

        $this->assertDatabaseHas('orders', [
            'id' => $unpaidOrder->id,
            'payment_status' => 'unpaid',
            'order_status' => 'processing',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $cancelledOrder->id,
            'payment_status' => 'paid',
            'order_status' => 'cancelled',
        ]);
    }

    #[DataProvider('rolesWithoutCashierActionAccess')]
    public function test_users_without_cashier_role_cannot_change_online_order_status(string $role): void
    {
        $user = User::factory()->create(['role' => $role, 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $order = $this->createOnlineOrder($customer, 'ONL-ROLE-001', 'paid', 'processing');

        $this->actingAs($user)
            ->post(route('cashier.orders.ready', $order->order_number))
            ->assertForbidden();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'processing',
        ]);
    }

    public function test_cashier_dashboard_and_history_link_to_online_order_details(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $order = $this->createOnlineOrder($customer, 'ONL-LINK-001', 'paid', 'packed');
        $this->actingAs($cashier);

        $this->get(route('cashier.index'))
            ->assertOk()
            ->assertSee(route('cashier.orders.show', $order->order_number), false)
            ->assertSee('SIAP DIAMBIL');

        $this->get(route('cashier.transactions'))
            ->assertOk()
            ->assertSee(route('cashier.orders.show', $order->order_number), false)
            ->assertSee('SIAP DIAMBIL');
    }

    public function test_cashier_detail_route_does_not_show_offline_orders(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $order = Order::query()->create([
            'order_number' => 'POS-DETAIL-001',
            'type' => 'offline',
            'cashier_id' => $cashier->id,
            'subtotal' => 25000,
            'discount' => 0,
            'total' => 25000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);

        $this->actingAs($cashier)
            ->get(route('cashier.orders.show', $order->order_number))
            ->assertNotFound();
    }

    public static function rolesWithoutCashierActionAccess(): array
    {
        return [
            'owner' => ['owner'],
            'customer' => ['customer'],
        ];
    }

    private function createOnlineOrder(
        User $customer,
        string $orderNumber,
        string $paymentStatus,
        string $orderStatus
    ): Order {
        return Order::query()->create([
            'order_number' => $orderNumber,
            'type' => 'online',
            'customer_id' => $customer->id,
            'subtotal' => 25000,
            'discount' => 0,
            'total' => 25000,
            'payment_method' => 'midtrans_qris',
            'payment_status' => $paymentStatus,
            'order_status' => $orderStatus,
            'midtrans_order_id' => $orderNumber,
        ]);
    }

    private function createProduct(): Product
    {
        $category = Category::query()->create([
            'name' => 'Sembako',
            'slug' => 'sembako',
            'is_active' => true,
        ]);

        return Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Beras Premium',
            'slug' => 'beras-premium',
            'barcode' => 'BR001',
            'cost_price' => 9000,
            'sell_price' => 12500,
            'stock' => 10,
            'min_stock' => 2,
            'unit' => 'kg',
            'is_active' => true,
        ]);
    }
}

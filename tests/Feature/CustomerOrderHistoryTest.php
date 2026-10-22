<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_without_orders_sees_an_empty_state_and_shopping_link(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $response = $this->actingAs($customer)->get(route('orders.index'));

        $response->assertOk()
            ->assertSee('Kamu belum memiliki riwayat pemesanan.')
            ->assertSee(route('catalog'), false);
    }

    public function test_customer_sees_only_their_online_orders_with_newest_first(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $otherCustomer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $olderOrder = $this->createOrder($customer, 'ONL-HISTORY-OLD', 'processing', 'paid');
        $newerOrder = $this->createOrder($customer, 'ONL-HISTORY-NEW', 'packed', 'paid');
        $this->createOrder($otherCustomer, 'ONL-PRIVATE-ORDER', 'completed', 'paid');
        $this->createProductItem($olderOrder, 'Old Product', 1);
        $this->createProductItem($newerOrder, 'New Product', 2);
        Order::query()->whereKey($olderOrder->id)->update(['created_at' => now()->subDay()]);
        Order::query()->whereKey($newerOrder->id)->update(['created_at' => now()]);

        $response = $this->actingAs($customer)->get(route('orders.index'));

        $response->assertOk()
            ->assertSeeInOrder(['ONL-HISTORY-NEW', 'ONL-HISTORY-OLD'])
            ->assertSee('Siap Diambil')
            ->assertSee('New Product')
            ->assertDontSee('ONL-PRIVATE-ORDER')
            ->assertSee(route('orders.show', $newerOrder->order_number), false);
    }

    public function test_customer_can_search_and_filter_their_order_history(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $this->createOrder($customer, 'ONL-FILTER-PROCESSING', 'processing', 'paid');
        $matchingOrder = $this->createOrder($customer, 'ONL-FILTER-COMPLETE', 'completed', 'paid');

        $response = $this->actingAs($customer)->get(route('orders.index', [
            'search' => 'FILTER-COMPLETE',
            'status' => 'completed',
        ]));

        $response->assertOk()
            ->assertSee('ONL-FILTER-COMPLETE')
            ->assertDontSee('ONL-FILTER-PROCESSING')
            ->assertSee(route('orders.show', $matchingOrder->order_number), false);
    }

    public function test_invalid_order_status_filter_is_rejected(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($customer)
            ->get(route('orders.index', ['status' => 'payment_failed']))
            ->assertSessionHasErrors('status');
    }

    public function test_customer_can_view_owned_order_details_using_saved_transaction_values(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $order = $this->createOrder($customer, 'ONL-DETAIL-CUSTOMER', 'processing', 'paid');
        $product = $this->createProductItem($order, 'Snapshot Product', 2);
        $product->update(['sell_price' => 50000]);

        $response = $this->actingAs($customer)->get(route('orders.show', $order->order_number));

        $response->assertOk()
            ->assertSee('Pembayaran: Berhasil')
            ->assertSee('Sedang Diproses')
            ->assertSee('Snapshot Product')
            ->assertSee('Rp 12.500')
            ->assertSee('Rp 25.000')
            ->assertDontSee('Rp 50.000')
            ->assertSee('Perkembangan Pesanan');
    }

    public function test_customer_cannot_view_another_customers_order_details(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $otherCustomer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $order = $this->createOrder($otherCustomer, 'ONL-PRIVATE-DETAIL', 'processing', 'paid');

        $this->actingAs($customer)
            ->get(route('orders.show', $order->order_number))
            ->assertNotFound();
    }

    public function test_expired_payment_is_not_presented_as_waiting_for_payment(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $order = $this->createOrder($customer, 'ONL-EXPIRED-PAYMENT', 'pending_payment', 'expired');

        $this->actingAs($customer)
            ->get(route('orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('Pembayaran Kedaluwarsa')
            ->assertSee('Kedaluwarsa')
            ->assertDontSee('Menunggu Pembayaran QRIS');
    }

    public function test_guest_can_still_open_an_unclaimed_checkout_order(): void
    {
        $order = $this->createOrder(null, 'ONL-GUEST-ORDER', 'pending_payment', 'pending');

        $this->get(route('orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('ONL-GUEST-ORDER');
    }

    public function test_customer_profile_links_to_order_history(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($customer)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Informasi Profil')
            ->assertSee('Riwayat Pemesanan')
            ->assertSee(route('orders.index'), false);
    }

    public function test_order_history_shows_the_latest_status_after_cashier_updates_it(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $order = $this->createOrder($customer, 'ONL-UPDATED-STATUS', 'processing', 'paid');

        $this->actingAs($cashier)
            ->post(route('cashier.orders.ready', $order->order_number))
            ->assertRedirect(route('cashier.orders.show', $order->order_number));

        $this->actingAs($customer)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Siap Diambil')
            ->assertSee('ONL-UPDATED-STATUS');

        $this->get(route('orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('Status pesanan saat ini')
            ->assertSee('Siap Diambil');
    }

    private function createOrder(
        ?User $customer,
        string $orderNumber,
        string $orderStatus,
        string $paymentStatus
    ): Order {
        return Order::query()->create([
            'order_number' => $orderNumber,
            'type' => 'online',
            'customer_id' => $customer?->id,
            'subtotal' => 25000,
            'discount' => 0,
            'total' => 25000,
            'payment_method' => 'midtrans_qris',
            'payment_status' => $paymentStatus,
            'order_status' => $orderStatus,
            'midtrans_order_id' => $orderNumber,
            'paid_at' => $paymentStatus === 'paid' ? now() : null,
        ]);
    }

    private function createProductItem(Order $order, string $productName, int $quantity): Product
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'customer-history'],
            ['name' => 'Riwayat', 'is_active' => true]
        );
        $slug = str($productName)->slug('-')->toString();
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => $productName,
            'slug' => $slug,
            'barcode' => strtoupper($slug),
            'cost_price' => 9000,
            'sell_price' => 12500,
            'stock' => 10,
            'min_stock' => 1,
            'unit' => 'pcs',
            'is_active' => true,
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $productName,
            'price' => 12500,
            'cost_price' => 9000,
            'qty' => $quantity,
            'subtotal' => 12500 * $quantity,
        ]);

        return $product;
    }
}

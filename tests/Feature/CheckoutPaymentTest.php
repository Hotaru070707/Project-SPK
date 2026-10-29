<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_must_log_in_before_adding_a_product_to_the_cart(): void
    {
        $product = $this->createProduct();

        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'qty' => 1,
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Silakan masuk sebagai pelanggan terlebih dahulu untuk menambahkan produk ke keranjang.')
            ->assertSessionMissing('cart')
            ->assertSessionMissing('cart_count');
    }

    public function test_cashier_cannot_add_products_to_the_cart(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $product = $this->createProduct();

        $response = $this->actingAs($cashier)->post(route('cart.add'), [
            'product_id' => $product->id,
            'qty' => 1,
        ]);

        $response->assertForbidden()
            ->assertSessionMissing('cart')
            ->assertSessionMissing('cart_count');
    }

    public function test_owner_is_redirected_away_from_adding_products_to_the_cart(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $product = $this->createProduct();

        $response = $this->actingAs($owner)->post(route('cart.add'), [
            'product_id' => $product->id,
            'qty' => 1,
        ]);

        $response->assertRedirect(route('admin.dashboard'))
            ->assertSessionMissing('cart')
            ->assertSessionMissing('cart_count');
    }

    public function test_customer_can_add_a_product_to_the_cart(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $product = $this->createProduct();

        $response = $this->actingAs($customer)->post(route('cart.add'), [
            'product_id' => $product->id,
            'qty' => 1,
        ]);

        $response->assertSessionHas('success', "{$product->name} berhasil ditambahkan ke keranjang!")
            ->assertSessionHas('cart_count', 1)
            ->assertSessionHas("cart.{$product->id}.qty", 1);
    }

    public function test_checkout_only_accepts_qris_or_cash(): void
    {
        $product = $this->createProduct();

        $response = $this->withSession(['cart' => [$product->id => $this->cartItem($product)]])
            ->post(route('cart.checkout.process'), [
                'recipient_name' => 'Test Customer',
                'phone' => '081234567890',
                'payment_method' => 'manual_transfer',
            ]);

        $response->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_qris_checkout_creates_snap_session_with_qris_only(): void
    {
        config([
            'services.midtrans.server_key' => 'SB-Mid-server-test-key',
            'services.midtrans.snap_url' => 'https://app.sandbox.midtrans.com',
        ]);

        Http::fake([
            'app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v3/redirection/snap-token',
            ], 201),
        ]);

        $product = $this->createProduct();
        $idempotencyKey = (string) Str::uuid();
        $response = $this->withSession(['cart' => [$product->id => $this->cartItem($product)]])
            ->post(route('cart.checkout.process'), [
                'idempotency_key' => $idempotencyKey,
                'recipient_name' => 'Test Customer',
                'phone' => '081234567890',
                'payment_method' => 'midtrans_qris',
            ]);

        $response->assertRedirect('https://app.sandbox.midtrans.com/snap/v3/redirection/snap-token');
        $order = Order::query()->firstOrFail();
        $this->assertSame('midtrans_qris', $order->payment_method);
        $this->assertSame('snap-token', $order->midtrans_token);
        $this->assertSame('https://app.sandbox.midtrans.com/snap/v3/redirection/snap-token', data_get($order->midtrans_payload, 'redirect_url'));
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'type' => 'out',
            'qty' => 2,
            'stock_before' => 5,
            'stock_after' => 3,
        ]);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
                && $request['enabled_payments'] === ['other_qris']
                && $request['transaction_details']['gross_amount'] === 25000
        );

        $this->post(route('cart.checkout.process'), [
            'idempotency_key' => $idempotencyKey,
            'recipient_name' => 'Test Customer',
            'phone' => '081234567890',
            'payment_method' => 'midtrans_qris',
        ])->assertRedirect('https://app.sandbox.midtrans.com/snap/v3/redirection/snap-token');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_qris_checkout_rejects_a_production_key_in_sandbox_mode(): void
    {
        config([
            'services.midtrans.server_key' => 'Mid-server-production-test-key',
            'services.midtrans.snap_url' => 'https://app.sandbox.midtrans.com',
        ]);
        Http::preventStrayRequests();

        $product = $this->createProduct();
        $response = $this->withSession(['cart' => [$product->id => $this->cartItem($product)]])
            ->post(route('cart.checkout.process'), [
                'recipient_name' => 'Test Customer',
                'phone' => '081234567890',
                'payment_method' => 'midtrans_qris',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Pesanan gagal dibuat. Periksa konfigurasi Midtrans atau coba kembali.');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_qris_checkout_rolls_back_reserved_stock_when_midtrans_connection_fails(): void
    {
        config([
            'services.midtrans.server_key' => 'SB-Mid-server-test-key',
            'services.midtrans.snap_url' => 'https://app.sandbox.midtrans.com',
        ]);
        Http::fake([
            'app.sandbox.midtrans.com/snap/v1/transactions' => Http::failedConnection(),
        ]);

        $product = $this->createProduct();
        $response = $this->withSession(['cart' => [$product->id => $this->cartItem($product)]])
            ->post(route('cart.checkout.process'), [
                'recipient_name' => 'Test Customer',
                'phone' => '081234567890',
                'payment_method' => 'midtrans_qris',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('error', 'Pesanan gagal dibuat. Periksa konfigurasi Midtrans atau coba kembali.');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_checkout_rejects_insufficient_stock_without_creating_an_order_or_movement(): void
    {
        $product = $this->createProduct();
        $cartItem = $this->cartItem($product);
        $cartItem['qty'] = 6;

        $this->withSession(['cart' => [$product->id => $cartItem]])
            ->from(route('cart.checkout'))
            ->post(route('cart.checkout.process'), [
                'recipient_name' => 'Test Customer',
                'phone' => '081234567890',
                'payment_method' => 'cash',
            ])
            ->assertRedirect(route('cart.checkout'))
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_expired_online_payment_restores_reserved_stock_once_and_ignores_late_settlement(): void
    {
        $serverKey = 'SB-Mid-server-test-key';
        config(['services.midtrans.server_key' => $serverKey]);
        $product = $this->createProduct();
        $order = Order::query()->create([
            'order_number' => 'ONL-EXPIRED-001',
            'type' => 'online',
            'subtotal' => 25000,
            'discount' => 0,
            'total' => 25000,
            'payment_method' => 'midtrans_qris',
            'payment_status' => 'unpaid',
            'order_status' => 'pending_payment',
            'midtrans_order_id' => 'ONL-EXPIRED-001',
        ]);
        app(StockService::class)->setStock(
            $product,
            3,
            'out',
            null,
            'online_order:'.$order->order_number,
            $order->id
        );

        $notification = [
            'order_id' => $order->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '25000.00',
            'transaction_status' => 'expire',
        ];
        $notification['signature_key'] = hash(
            'sha512',
            $notification['order_id'].$notification['status_code'].$notification['gross_amount'].$serverKey
        );

        $this->postJson(route('midtrans.notification'), $notification)->assertOk();
        $this->assertSame('expired', $order->fresh()->payment_status);
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'type' => 'return',
            'qty' => 2,
            'stock_before' => 3,
            'stock_after' => 5,
        ]);
        $this->assertDatabaseCount('stock_movements', 2);

        $notification['transaction_status'] = 'settlement';
        $notification['signature_key'] = hash(
            'sha512',
            $notification['order_id'].$notification['status_code'].$notification['gross_amount'].$serverKey
        );

        $this->postJson(route('midtrans.notification'), $notification)->assertOk();

        $this->assertSame('expired', $order->fresh()->payment_status);
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_cash_checkout_does_not_call_midtrans(): void
    {
        Http::preventStrayRequests();

        $product = $this->createProduct();
        $response = $this->withSession(['cart' => [$product->id => $this->cartItem($product)]])
            ->post(route('cart.checkout.process'), [
                'recipient_name' => 'Test Customer',
                'phone' => '081234567890',
                'payment_method' => 'cash',
            ]);

        $order = Order::query()->firstOrFail();
        $response->assertRedirect(route('orders.show', $order->order_number));
        $this->assertSame('pending', $order->payment_status);
        $this->assertNull($order->midtrans_order_id);
    }

    public function test_customer_checkout_ignores_legacy_member_tier_and_charges_full_subtotal(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);
        DB::table('users')->where('id', $customer->id)->update(['member_tier' => 'silver']);
        $product = $this->createProduct();

        $response = $this->actingAs($customer)
            ->withSession(['cart' => [$product->id => $this->cartItem($product)]])
            ->post(route('cart.checkout.process'), [
                'recipient_name' => 'Test Customer',
                'phone' => '081234567890',
                'payment_method' => 'cash',
            ]);

        $order = Order::query()->firstOrFail();
        $response->assertRedirect(route('orders.show', $order->order_number));

        $this->assertSame('0.00', $order->discount);
        $this->assertSame('25000.00', $order->total);
    }

    public function test_midtrans_notification_requires_a_valid_signature_and_marks_order_paid(): void
    {
        $serverKey = 'SB-Mid-server-test-key';
        config(['services.midtrans.server_key' => $serverKey]);

        $order = Order::query()->create([
            'order_number' => 'ONL-TEST-001',
            'type' => 'online',
            'subtotal' => 25000,
            'discount' => 0,
            'total' => 25000,
            'payment_method' => 'midtrans_qris',
            'payment_status' => 'unpaid',
            'order_status' => 'pending_payment',
            'midtrans_order_id' => 'ONL-TEST-001',
        ]);

        $payload = [
            'order_id' => $order->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '25000.00',
            'transaction_status' => 'settlement',
        ];
        $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$serverKey);

        $this->postJson(route('midtrans.notification'), [...$payload, 'signature_key' => 'invalid-signature'])
            ->assertForbidden();
        $this->assertSame('unpaid', $order->fresh()->payment_status);

        $this->postJson(route('midtrans.notification'), $payload)
            ->assertOk();
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('processing', $order->fresh()->order_status);
    }

    public function test_repeated_midtrans_settlement_notification_does_not_change_reserved_stock_again(): void
    {
        $serverKey = 'SB-Mid-server-test-key';
        config(['services.midtrans.server_key' => $serverKey]);
        $product = $this->createProduct();
        $order = Order::query()->create([
            'order_number' => 'ONL-REPEATED-SETTLEMENT',
            'type' => 'online',
            'subtotal' => 25000,
            'discount' => 0,
            'total' => 25000,
            'payment_method' => 'midtrans_qris',
            'payment_status' => 'unpaid',
            'order_status' => 'pending_payment',
            'midtrans_order_id' => 'ONL-REPEATED-SETTLEMENT',
        ]);
        app(StockService::class)->setStock(
            $product,
            3,
            'out',
            null,
            'online_order:'.$order->order_number,
            $order->id
        );
        $notification = [
            'order_id' => $order->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '25000.00',
            'transaction_status' => 'settlement',
        ];
        $notification['signature_key'] = hash(
            'sha512',
            $notification['order_id'].$notification['status_code'].$notification['gross_amount'].$serverKey
        );

        $this->postJson(route('midtrans.notification'), $notification)->assertOk();
        $this->postJson(route('midtrans.notification'), $notification)->assertOk();

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('processing', $order->fresh()->order_status);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_returning_from_midtrans_refreshes_payment_status_from_gateway(): void
    {
        $serverKey = 'SB-Mid-server-test-key';
        config([
            'services.midtrans.server_key' => $serverKey,
            'services.midtrans.api_url' => 'https://api.sandbox.midtrans.com',
        ]);
        Http::preventStrayRequests();

        $order = Order::query()->create([
            'order_number' => 'ONL-TEST-002',
            'type' => 'online',
            'subtotal' => 25000,
            'discount' => 0,
            'total' => 25000,
            'payment_method' => 'midtrans_qris',
            'payment_status' => 'unpaid',
            'order_status' => 'pending_payment',
            'midtrans_order_id' => 'ONL-TEST-002',
        ]);

        $notification = [
            'order_id' => $order->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '25000.00',
            'transaction_status' => 'settlement',
        ];
        $notification['signature_key'] = hash(
            'sha512',
            $notification['order_id']
            .$notification['status_code']
            .$notification['gross_amount']
            .$serverKey
        );

        Http::fake([
            'api.sandbox.midtrans.com/v2/ONL-TEST-002/status' => Http::response(
                $notification,
                200
            ),
        ]);

        $this->get(route('orders.show', [
            'orderNumber' => $order->order_number,
            'midtrans_return' => 1,
        ]))->assertOk();

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('processing', $order->fresh()->order_status);

        Http::assertSent(fn (Request $request): bool => $request->url()
            === 'https://api.sandbox.midtrans.com/v2/ONL-TEST-002/status'
            && $request->hasHeader('Authorization'));
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
            'cost_price' => 12500,
            'sell_price' => 12500,
            'stock' => 5,
            'min_stock' => 2,
            'unit' => 'kg',
            'is_active' => true,
        ]);
    }

    private function cartItem(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->sell_price,
            'cost_price' => (float) $product->cost_price,
            'qty' => 2,
        ];
    }
}

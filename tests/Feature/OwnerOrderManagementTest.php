<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_order_list_shows_database_summaries_and_transactions_without_proof_controls(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'name' => 'Transfer Pelanggan']);
        $manualPending = $this->createOrder('ONL-MANUAL-PENDING', [
            'customer_id' => $customer->id,
            'payment_method' => 'manual_transfer',
            'payment_status' => 'pending',
            'payment_verification_status' => 'pending',
            'payment_proof' => 'payment-proofs/manual.pdf',
        ]);
        $manualAwaitingProof = $this->createOrder('ONL-MANUAL-AWAITING', [
            'payment_method' => 'manual_transfer',
            'payment_verification_status' => 'awaiting_proof',
        ]);
        $voidOrder = $this->createOrder('ONL-LEGACY-VOID', [
            'payment_method' => 'midtrans_qris',
            'payment_status' => 'expired',
            'order_status' => 'void',
            'midtrans_order_id' => 'ONL-LEGACY-VOID',
        ]);
        $this->createOrder('ONL-QRIS-PENDING', [
            'payment_method' => 'midtrans_qris',
            'payment_status' => 'unpaid',
            'midtrans_order_id' => 'ONL-QRIS-PENDING',
        ]);
        $this->createOrder('POS-CASH-COMPLETED', [
            'type' => 'offline',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);
        $this->createOrder('ONL-PROCESSING', [
            'payment_method' => 'midtrans_qris',
            'payment_status' => 'paid',
            'order_status' => 'processing',
            'midtrans_order_id' => 'ONL-PROCESSING',
        ]);

        $this->actingAs($owner)
            ->get(route('admin.orders'))
            ->assertOk()
            ->assertSee('Total Pesanan')
            ->assertSee('Menunggu Pembayaran')
            ->assertSee('Sedang Diproses')
            ->assertSee('Pesanan Selesai')
            ->assertSee('ONL-MANUAL-PENDING')
            ->assertSee('ONL-MANUAL-AWAITING')
            ->assertSee('ONL-QRIS-PENDING')
            ->assertSee('POS-CASH-COMPLETED')
            ->assertSee('ONL-PROCESSING')
            ->assertSee('Pesanan & Transaksi')
            ->assertDontSee('Perlu Verifikasi Manual')
            ->assertDontSee('Lihat Bukti')
            ->assertDontSee('Belum ada bukti')
            ->assertDontSee('Tidak diperlukan')
            ->assertDontSee('<option value="void"', false)
            ->assertSee('Void (dibatalkan)')
            ->assertSee('>6</p>', false)
            ->assertSee('>3</p>', false)
            ->assertDontSee('>2</p>', false);

        $this->get(route('admin.orders', [
            'payment_status' => 'pending',
            'order_status' => 'cancelled',
            'search' => 'Transfer Pelanggan',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
        ]))->assertOk()
            ->assertDontSee($manualPending->order_number);

        $this->get(route('admin.orders', ['order_status' => 'cancelled']))
            ->assertOk()
            ->assertSee($voidOrder->order_number);
    }

    public function test_owner_can_filter_orders_by_customer_invoice_status_and_date(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'name' => 'Dina Pelanggan']);
        $matchingOrder = $this->createOrder('ONL-SEARCH-MATCH', [
            'customer_id' => $customer->id,
            'payment_method' => 'midtrans_qris',
            'payment_status' => 'paid',
            'order_status' => 'processing',
            'midtrans_order_id' => 'ONL-SEARCH-MATCH',
        ]);
        $rejectedCustomer = User::factory()->create(['role' => 'customer', 'name' => 'Dina Pelanggan Ditolak']);
        $rejectedOrder = $this->createOrder('ONL-SEARCH-REJECTED', [
            'customer_id' => $rejectedCustomer->id,
            'payment_status' => 'pending',
            'order_status' => 'cancelled',
            'payment_verification_status' => 'rejected',
            'payment_rejection_reason' => 'Bukti tidak terbaca.',
        ]);
        $this->createOrder('ONL-SEARCH-OTHER', [
            'payment_method' => 'manual_transfer',
            'payment_verification_status' => 'pending',
            'payment_proof' => 'payment-proofs/other.pdf',
        ]);

        $this->actingAs($owner)
            ->get(route('admin.orders', [
                'search' => 'Dina Pelanggan',
                'type' => 'online',
                'payment_status' => 'paid',
                'order_status' => 'processing',
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($matchingOrder->order_number)
            ->assertDontSee('ONL-SEARCH-OTHER');

        $this->get(route('admin.orders', [
            'search' => 'Dina Pelanggan Ditolak',
            'type' => 'online',
            'payment_status' => 'pending',
            'order_status' => 'cancelled',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
        ]))->assertOk()
            ->assertSee($rejectedOrder->order_number)
            ->assertDontSee($matchingOrder->order_number);
    }

    public function test_manual_transfer_can_be_confirmed_without_file_upload_and_keeps_detail_history(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $order = $this->createOrder('ONL-MANUAL-CONFIRM', [
            'customer_id' => $customer->id,
            'payment_method' => 'manual_transfer',
            'payment_verification_status' => 'awaiting_proof',
        ]);
        $product = $this->createProduct();
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 15000,
            'cost_price' => 10000,
            'qty' => 1,
            'subtotal' => 15000,
        ]);

        $this->actingAs($customer)
            ->get(route('orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('Ikuti instruksi pembayaran resmi dari toko.')
            ->assertDontSee('type="file"', false);

        $this->actingAs($owner)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee($product->name)
            ->assertSee('Rincian Barang')
            ->assertSee('Mutasi Stok Terkait Pesanan');

        $this->from(route('admin.orders.show', $order))
            ->post(route('admin.orders.payment.confirm', $order))
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('processing', $order->fresh()->order_status);
        $this->assertSame('approved', $order->fresh()->payment_verification_status);
        $this->assertSame($owner->id, $order->fresh()->payment_reviewed_by);
        $this->assertNotNull($order->fresh()->payment_reviewed_at);
    }

    public function test_manual_transfer_confirmation_does_not_confirm_qris_or_pos_cash(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $manualOrder = $this->createOrder('ONL-REJECT-REASON', [
            'payment_method' => 'manual_transfer',
            'payment_verification_status' => 'pending',
        ]);
        $qrisOrder = $this->createOrder('ONL-QRIS-MANUAL-BLOCK', [
            'payment_method' => 'midtrans_qris',
            'payment_status' => 'unpaid',
            'midtrans_order_id' => 'ONL-QRIS-MANUAL-BLOCK',
        ]);
        $posOrder = $this->createOrder('POS-CASH-MANUAL-BLOCK', [
            'type' => 'offline',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);

        $this->actingAs($owner)
            ->from(route('admin.orders'))
            ->post(route('admin.orders.payment.confirm', $manualOrder))
            ->assertRedirect(route('admin.orders'))
            ->assertSessionHas('success');

        $this->post(route('admin.orders.payment.confirm', $qrisOrder))
            ->assertRedirect(route('admin.orders'))
            ->assertSessionHas('error');
        $this->post(route('admin.orders.payment.confirm', $posOrder))
            ->assertRedirect(route('admin.orders'))
            ->assertSessionHas('error');

        $this->assertSame('paid', $manualOrder->fresh()->payment_status);
        $this->assertSame('unpaid', $qrisOrder->fresh()->payment_status);
        $this->assertSame('paid', $posOrder->fresh()->payment_status);
    }

    public function test_cashier_can_confirm_manual_transfer_but_cannot_access_owner_order_management(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $order = $this->createOrder('ONL-CASHIER-MANUAL', [
            'payment_method' => 'manual_transfer',
        ]);

        $this->actingAs($cashier)
            ->get(route('admin.orders'))
            ->assertForbidden();

        $this->post(route('cashier.orders.payment.confirm', $order->order_number))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('processing', $order->fresh()->order_status);
        $this->assertSame($cashier->id, $order->fresh()->payment_reviewed_by);
    }

    public function test_legacy_payment_proof_endpoints_are_no_longer_available(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $order = $this->createOrder('ONL-LEGACY-PROOF', [
            'customer_id' => $customer->id,
            'payment_proof' => 'payment-proofs/legacy.pdf',
        ]);

        $this->actingAs($customer)
            ->post('/akun/pesanan/'.$order->order_number.'/bukti-transfer')
            ->assertNotFound();

        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $this->actingAs($owner)
            ->get('/admin/pesanan/'.$order->id.'/bukti')
            ->assertNotFound();

        $this->assertSame('payment-proofs/legacy.pdf', $order->fresh()->payment_proof);
    }

    public function test_order_filter_rejects_an_invalid_date_range(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);

        $this->actingAs($owner)
            ->get(route('admin.orders', [
                'start_date' => '2026-10-10',
                'end_date' => '2026-10-09',
            ]))
            ->assertSessionHasErrors('end_date');
    }

    private function createOrder(string $orderNumber, array $attributes = []): Order
    {
        return Order::query()->create(array_merge([
            'order_number' => $orderNumber,
            'type' => 'online',
            'subtotal' => 25000,
            'discount' => 0,
            'total' => 25000,
            'payment_method' => 'manual_transfer',
            'payment_status' => 'unpaid',
            'order_status' => 'pending_payment',
            'payment_verification_status' => 'awaiting_proof',
        ], $attributes));
    }

    private function createProduct(): Product
    {
        $category = Category::query()->create([
            'name' => 'Sembako',
            'slug' => 'order-detail-category',
        ]);

        return Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Beras Detail',
            'slug' => 'beras-detail',
            'cost_price' => 10000,
            'sell_price' => 15000,
            'stock' => 5,
            'min_stock' => 1,
            'unit' => 'kg',
            'is_active' => true,
        ]);
    }
}

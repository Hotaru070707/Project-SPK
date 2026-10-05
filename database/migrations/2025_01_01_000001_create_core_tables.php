<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. CATEGORIES
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. PRODUCTS
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('barcode', 64)->nullable()->index();
            $table->text('description')->nullable();
            $table->decimal('cost_price', 15, 2); // Harga Modal
            $table->decimal('sell_price', 15, 2); // Harga Jual
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->string('unit', 20)->default('pcs');
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['category_id', 'is_active']);
        });

        // 3. ADDRESSES (Buku Alamat Pelanggan)
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->default('Rumah');
            $table->string('recipient_name');
            $table->string('phone', 20);
            $table->text('full_address');
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });



        // 5. PROMOS / VOUCHERS
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->enum('type', ['percent', 'fixed', 'product_discount'])->default('percent');
            $table->decimal('value', 15, 2);
            $table->decimal('min_purchase', 15, 2)->default(0);
            $table->integer('max_uses')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. ORDERS (Online & Offline POS)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 40)->unique();
            $table->enum('type', ['online', 'offline'])->default('online');
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('promo_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('payment_method', ['cash', 'manual_transfer', 'midtrans_qris', 'midtrans_va', 'midtrans_ewallet'])->default('cash');
            $table->enum('payment_status', ['unpaid', 'pending', 'paid', 'expired', 'refunded'])->default('unpaid');
            $table->enum('order_status', ['pending_payment', 'processing', 'packed', 'shipped', 'completed', 'cancelled', 'void'])->default('pending_payment');
            $table->string('midtrans_order_id', 80)->nullable()->index();
            $table->string('midtrans_token')->nullable();
            $table->json('midtrans_payload')->nullable();
            $table->string('payment_proof')->nullable();
            $table->string('shipping_tracking')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['type', 'order_status', 'payment_status']);
            $table->index('created_at');
        });

        // 7. ORDER ITEMS
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name');
            $table->decimal('price', 15, 2);
            $table->decimal('cost_price', 15, 2);
            $table->integer('qty');
            $table->decimal('subtotal', 15, 2);
            $table->enum('item_status', ['normal', 'returned', 'refunded'])->default('normal');
            $table->timestamps();
        });

        // 8. STOCK MOVEMENTS (Audit Trail Mutasi Stok)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment', 'return']);
            $table->integer('qty');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // 9. CARTS & CART ITEMS
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id', 100)->nullable()->index();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('qty')->default(1);
            $table->timestamps();
            $table->unique(['cart_id', 'product_id']);
        });

        // 10. REVIEWS (Ulasan & Komplain Retur Produk)
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('rating')->default(5);
            $table->text('comment')->nullable();
            $table->boolean('is_complaint')->default(false);
            $table->timestamps();
        });

        // 11. TOPSIS CRITERIA
        Schema::create('topsis_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // C1..C6
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['benefit', 'cost']);
            $table->decimal('weight', 5, 4); // Misal 0.2500
            $table->string('data_source')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 12. TOPSIS RESULTS (Snapshot Riwayat Kalkulasi)
        Schema::create('topsis_results', function (Blueprint $table) {
            $table->id();
            $table->string('period_start')->nullable();
            $table->string('period_end')->nullable();
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->json('criteria_snapshot');
            $table->json('decision_matrix');
            $table->json('normalized_matrix');
            $table->json('weighted_matrix');
            $table->json('ideal_positive');
            $table->json('ideal_negative');
            $table->json('distances');
            $table->json('preferences');
            $table->timestamps();
        });

        // 13. WHATSAPP LOGS
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20);
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->nullableMorphs('related');
            $table->timestamps();
        });

        // 14. SETTINGS (Key-Value Konfigurasi)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('whatsapp_logs');
        Schema::dropIfExists('topsis_results');
        Schema::dropIfExists('topsis_criteria');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('promos');

        Schema::dropIfExists('addresses');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};

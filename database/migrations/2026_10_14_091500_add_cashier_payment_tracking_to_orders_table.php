<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('idempotency_key')->nullable()->unique()->after('order_number');
            $table->decimal('cash_given', 15, 2)->nullable()->after('total');
            $table->decimal('change_amount', 15, 2)->nullable()->after('cash_given');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn(['idempotency_key', 'cash_given', 'change_amount']);
        });
    }
};

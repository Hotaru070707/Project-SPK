<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_verification_status', 24)->default('not_required');
            $table->text('payment_rejection_reason')->nullable();
            $table->foreignId('payment_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('payment_reviewed_at')->nullable();
        });

        DB::table('orders')
            ->where('payment_method', 'manual_transfer')
            ->update([
                'payment_verification_status' => DB::raw("
                    CASE
                        WHEN payment_status = 'paid' THEN 'approved'
                        WHEN order_status IN ('cancelled', 'void') OR payment_status IN ('expired', 'refunded') THEN 'not_required'
                        WHEN payment_proof IS NULL OR payment_proof = '' THEN 'awaiting_proof'
                        ELSE 'pending'
                    END
                "),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['payment_reviewed_by']);
            $table->dropColumn([
                'payment_verification_status',
                'payment_rejection_reason',
                'payment_reviewed_by',
                'payment_reviewed_at',
            ]);
        });
    }
};

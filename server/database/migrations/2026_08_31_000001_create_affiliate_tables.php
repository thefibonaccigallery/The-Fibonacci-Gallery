<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid()->unique();
            $table->foreignId('affiliate_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->decimal('order_amount', 10, 2);
            $table->decimal('commission_rate', 5, 2)->default(20.00);
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('available'); // available, paid_out
            $table->timestamps();

            $table->index('affiliate_id');
            $table->index('referred_user_id');
            $table->index('order_id');
            $table->index('status');
        });

        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid()->unique();
            $table->foreignId('affiliate_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('wallet_address');
            $table->string('network')->default('USDT BEP20');
            $table->string('status')->default('requested'); // requested, approved, rejected, completed
            $table->text('admin_note')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('affiliate_id');
            $table->index('status');
        });

        Schema::create('affiliate_commission_payout', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('affiliate_payout_id')->constrained('affiliate_payouts')->cascadeOnDelete();
            $table->foreignId('affiliate_commission_id')->constrained('affiliate_commissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['affiliate_payout_id', 'affiliate_commission_id'], 'payout_commission_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_commission_payout');
        Schema::dropIfExists('affiliate_payouts');
        Schema::dropIfExists('affiliate_commissions');
    }
};

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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid()->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->string('nowpayments_payment_id')->unique()->nullable();
            $table->string('nowpayments_order_id')->nullable();
            $table->string('pay_currency')->nullable();
            $table->string('pay_address')->nullable();
            $table->decimal('pay_amount', 20, 8)->nullable();
            $table->decimal('actually_paid', 20, 8)->default(0);
            $table->string('status')->default('waiting');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index('nowpayments_payment_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

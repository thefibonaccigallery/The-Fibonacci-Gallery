<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid()->unique();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('code', 50)->unique();
            $table->decimal('discount_percentage', 5, 2);
            $table->unsignedInteger('max_uses')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('course_id');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};

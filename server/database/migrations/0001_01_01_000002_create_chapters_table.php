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
        Schema::create('chapters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid()->unique();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('title');
            $table->string('level')->nullable();
            $table->integer('length_seconds')->default(0);
            $table->integer('order')->default(1);
            $table->timestamps();
            $table->index('course_id');

            $table->unique(['course_id', 'order'], 'chapters_course_id_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};

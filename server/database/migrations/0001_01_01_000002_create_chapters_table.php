<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid()->unique()->default(DB::raw('gen_random_uuid()'));
            $table->bigInteger('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('title');
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->nullable();
            $table->integer('length_seconds')->default(0);
            $table->integer('order')->default(1);
            $table->timestamps();
            $table->index('course_id');
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

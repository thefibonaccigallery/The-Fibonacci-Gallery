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
        Schema::create('course_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid()->unique();
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->string('title');
            $table->string('youtube_video_id', 20)->nullable();
            $table->string('level')->nullable();
            $table->integer('order')->default(1);
            $table->integer('duration_seconds')->default(0);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_preview')->default(false);
            $table->boolean('is_big_five')->default(false);
            $table->integer('cooldown_hours')->default(0);
            $table->timestamps();
            $table->index('chapter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_videos');
    }
};

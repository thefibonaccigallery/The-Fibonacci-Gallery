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
        Schema::create('video_progress', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid()->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_video_id')->constrained('course_videos')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->integer('watch_time_seconds')->default(0);
            $table->decimal('watch_percentage', 5, 2)->default(0.00);
            $table->timestamp('last_watched_at')->nullable();
            $table->timestamp('cooldown_until')->nullable();
            $table->unique(['user_id', 'course_video_id']);
            $table->index('user_id');
            $table->index('course_video_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_progress');
    }
};

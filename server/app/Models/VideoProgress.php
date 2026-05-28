<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoProgress extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'video_progress';

    protected $fillable = [
        'user_id',
        'course_video_id',
        'is_completed',
        'completed_at',
        'watch_time_seconds',
        'watch_percentage',
        'last_watched_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'course_video_id' => 'integer',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'watch_time_seconds' => 'integer',
        'watch_percentage' => 'decimal:2',
        'last_watched_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function video()
    {
        return $this->belongsTo(CourseVideo::class, 'course_video_id');
    }
}

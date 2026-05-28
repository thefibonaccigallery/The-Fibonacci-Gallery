<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseVideo extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'chapter_id',
        'title',
        'youtube_video_id',
        'level',
        'order',
        'duration_seconds',
        'is_published',
        'is_preview',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
        'order' => 'integer',
        'duration_seconds' => 'integer',
        'is_published' => 'boolean',
        'is_preview' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($video) {
            if (empty($video->order)) {
                $maxOrder = static::where('chapter_id', $video->chapter_id)->max('order');
                $video->order = $maxOrder ? $maxOrder + 1 : 1;
            }
        });

        static::saved(function ($video) {
            $video->chapter->updateLengthSeconds();
        });

        static::deleted(function ($video) {
            $video->chapter->updateLengthSeconds();
        });
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function videoProgress()
    {
        return $this->hasMany(VideoProgress::class, 'video_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopePreview($query)
    {
        return $query->where('is_preview', true);
    }
}

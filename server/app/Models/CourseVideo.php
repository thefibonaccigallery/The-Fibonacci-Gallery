<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseVideo extends Model
{
    use HasFactory, HasUuid;

    protected $hidden = ['id', 'chapter_id'];

    protected $fillable = [
        'chapter_id',
        'title',
        'youtube_video_id',
        'level',
        'order',
        'duration_seconds',
        'cooldown_hours',
        'is_published',
        'is_preview',
        'is_big_five',
        'isBigFive',
    ];

    protected function casts(): array
    {
        return [
            'chapter_id'       => 'integer',
            'order'            => 'integer',
            'duration_seconds' => 'integer',
            'cooldown_hours'   => 'integer',
            'is_published'     => 'boolean',
            'is_preview'       => 'boolean',
            'is_big_five'      => 'boolean',
        ];
    }

    public function getIsBigFiveAttribute(): bool
    {
        return (bool) ($this->attributes['is_big_five'] ?? false);
    }

    public function setIsBigFiveAttribute($value): void
    {
        $this->attributes['is_big_five'] = (bool) $value;
    }

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
        return $this->hasMany(VideoProgress::class, 'course_video_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopePreview($query)
    {
        return $query->where('is_preview', true);
    }

    public function scopeBigFive($query)
    {
        return $query->where('is_big_five', true);
    }
}

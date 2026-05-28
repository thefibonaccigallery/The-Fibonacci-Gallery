<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'course_id',
        'title',
        'level',
        'length_seconds',
        'order',
    ];

    protected $casts = [
        'course_id' => 'integer',
        'length_seconds' => 'integer',
        'order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($chapter) {
            if (empty($chapter->order)) {
                $maxOrder = static::where('course_id', $chapter->course_id)->max('order');
                $chapter->order = $maxOrder ? $maxOrder + 1 : 1;
            }
        });

        static::saved(function ($chapter) {
            $chapter->course->updateLengthSeconds();
        });

        static::deleted(function ($chapter) {
            $chapter->course->updateLengthSeconds();
        });
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function videos()
    {
        return $this->hasMany(CourseVideo::class, 'chapter_id')->orderBy('order');
    }

    public function updateLengthSeconds()
    {
        $this->length_seconds = $this->videos()->sum('duration_seconds');
        $this->save();
    }
}

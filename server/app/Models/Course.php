<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory, HasUuid;

    protected $hidden = ['id'];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'thumbnail',
        'level',
        'length_seconds',
        'price',
        'what_is_included',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'price'            => 'decimal:2',
            'is_published'     => 'boolean',
            'published_at'     => 'datetime',
            'length_seconds'   => 'integer',
            'what_is_included' => 'array',
        ];
    }

    protected $appends = ['thumbnail_url'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });

        static::updating(function ($course) {
            if ($course->isDirty('title') && empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('order');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }
        return null;
    }

    public function updateLengthSeconds()
    {
        $this->length_seconds = $this->chapters()->sum('length_seconds');
        $this->save();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUnpublished($query)
    {
        return $query->where('is_published', false);
    }
}

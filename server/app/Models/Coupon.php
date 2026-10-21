<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'course_id',
        'code',
        'discount_percentage',
        'max_uses',
        'expires_at',
        'is_active',
        'show_on_homepage',
    ];

    protected function casts(): array
    {
        return [
            'course_id'           => 'integer',
            'discount_percentage' => 'decimal:2',
            'max_uses'            => 'integer',
            'expires_at'          => 'datetime',
            'is_active'           => 'boolean',
            'show_on_homepage'    => 'boolean',
        ];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->enrollments()->count() >= $this->max_uses) {
            return false;
        }

        return true;
    }
}

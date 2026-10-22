<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory, HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'user_id',
        'course_id',
        'enrolled_at',
        'completed_at',
        'progress_percentage',
        'type',
        'coupon_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id'             => 'integer',
            'course_id'           => 'integer',
            'enrolled_at'         => 'datetime',
            'completed_at'        => 'datetime',
            'progress_percentage' => 'decimal:2',
            'coupon_id'           => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class);
    }
}

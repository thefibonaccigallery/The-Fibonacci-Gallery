<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'course_id',
        'coupon_id',
        'amount',
        'currency',
        'nowpayments_payment_id',
        'nowpayments_order_id',
        'pay_currency',
        'pay_address',
        'pay_amount',
        'actually_paid',
        'status',
        'paid_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id'       => 'integer',
            'course_id'     => 'integer',
            'coupon_id'     => 'integer',
            'amount'        => 'decimal:2',
            'pay_amount'    => 'decimal:8',
            'actually_paid' => 'decimal:8',
            'paid_at'       => 'datetime',
            'expires_at'    => 'datetime',
        ];
    }

    protected $hidden = [
        'id',
        'user_id',
        'course_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}

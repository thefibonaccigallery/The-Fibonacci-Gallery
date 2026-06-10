<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'course_id',
        'amount',
        'currency',
        'nowpayments_payment_id',
        'nowpayments_order_id',
        'pay_currency',
        'pay_amount',
        'actually_paid',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'course_id' => 'integer',
        'amount' => 'decimal:2',
        'pay_amount' => 'decimal:8',
        'actually_paid' => 'decimal:8',
        'paid_at' => 'datetime',
    ];

    protected $hidden = [
        'id',
        'user_id',
        'course_id',
    ];
}

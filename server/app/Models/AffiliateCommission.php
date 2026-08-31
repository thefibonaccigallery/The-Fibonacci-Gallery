<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AffiliateCommission extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'affiliate_commissions';

    protected $fillable = [
        'affiliate_id',
        'referred_user_id',
        'order_id',
        'order_amount',
        'commission_rate',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'affiliate_id'     => 'integer',
            'referred_user_id' => 'integer',
            'order_id'         => 'integer',
            'order_amount'     => 'decimal:2',
            'commission_rate'  => 'decimal:2',
            'amount'           => 'decimal:2',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function payouts(): BelongsToMany
    {
        return $this->belongsToMany(
            AffiliatePayout::class,
            'affiliate_commission_payout',
            'affiliate_commission_id',
            'affiliate_payout_id'
        )->withTimestamps();
    }
}

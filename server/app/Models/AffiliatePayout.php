<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AffiliatePayout extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'affiliate_payouts';

    protected $fillable = [
        'affiliate_id',
        'amount',
        'wallet_address',
        'network',
        'status',
        'admin_note',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'affiliate_id' => 'integer',
            'amount'       => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function commissions(): BelongsToMany
    {
        return $this->belongsToMany(
            AffiliateCommission::class,
            'affiliate_commission_payout',
            'affiliate_payout_id',
            'affiliate_commission_id'
        )->withTimestamps();
    }
}

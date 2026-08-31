<?php

namespace App\Services;

use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AffiliateService
{
    /**
     * Process and create affiliate commission when an order is completed/finished.
     */
    public function processCommissionForOrder(Order $order): ?AffiliateCommission
    {
        if ($order->status !== 'finished' || (float) $order->amount <= 0) {
            return null;
        }

        // Avoid duplicate commission for the same order
        $existing = AffiliateCommission::where('order_id', $order->id)->first();
        if ($existing) {
            return $existing;
        }

        // Find paying user and their referrer
        $user = $order->user ?? User::find($order->user_id);
        if (!$user || !$user->referred_by) {
            return null;
        }

        // Referrer cannot be self
        if ($user->referred_by === $user->id) {
            return null;
        }

        $rate = (float) config('types.affiliate.commission_rate', 20);
        $commissionAmount = round(((float) $order->amount * $rate) / 100, 2);

        if ($commissionAmount <= 0) {
            return null;
        }

        return AffiliateCommission::create([
            'affiliate_id'     => $user->referred_by,
            'referred_user_id' => $user->id,
            'order_id'         => $order->id,
            'order_amount'     => $order->amount,
            'commission_rate'  => $rate,
            'amount'           => $commissionAmount,
            'status'           => 'available',
        ]);
    }

    /**
     * Get dashboard summary for an affiliate student.
     */
    public function getAffiliateSummary(User $user): array
    {
        $totalEarned = (float) AffiliateCommission::where('affiliate_id', $user->id)->sum('amount');
        $availableBalance = (float) AffiliateCommission::where('affiliate_id', $user->id)
            ->where('status', 'available')
            ->sum('amount');
        $paidOut = (float) AffiliatePayout::where('affiliate_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');
        $totalReferrals = User::where('referred_by', $user->id)->count();

        // Ensure user has a referral code
        if (empty($user->referral_code)) {
            $user->referral_code = User::generateUniqueReferralCode();
            $user->save();
        }

        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5174')), '/');
        $referralLink = "{$frontendUrl}/register?ref={$user->referral_code}";

        return [
            'total_earned'      => round($totalEarned, 2),
            'available_balance' => round($availableBalance, 2),
            'paid_out'          => round($paidOut, 2),
            'total_referrals'   => $totalReferrals,
            'referral_code'     => $user->referral_code,
            'referral_link'     => $referralLink,
            'commission_rate'   => (float) config('types.affiliate.commission_rate', 20),
        ];
    }

    /**
     * Request a payout bundling all available commissions.
     */
    public function requestPayout(User $user, string $walletAddress, string $network = 'USDT BEP20'): AffiliatePayout
    {
        return DB::transaction(function () use ($user, $walletAddress, $network) {
            $availableCommissions = AffiliateCommission::where('affiliate_id', $user->id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->get();

            $totalAvailable = (float) $availableCommissions->sum('amount');

            if ($availableCommissions->isEmpty() || $totalAvailable <= 0) {
                throw ValidationException::withMessages([
                    'amount' => ['You have no available commission balance to request a payout.'],
                ]);
            }

            $payout = AffiliatePayout::create([
                'affiliate_id'   => $user->id,
                'amount'         => $totalAvailable,
                'wallet_address' => $walletAddress,
                'network'        => $network,
                'status'         => 'requested',
            ]);

            $payout->commissions()->attach($availableCommissions->pluck('id'));

            AffiliateCommission::whereIn('id', $availableCommissions->pluck('id'))
                ->update(['status' => 'paid_out']);

            return $payout->fresh(['commissions']);
        });
    }

    /**
     * Admin approves a requested payout.
     */
    public function approvePayout(AffiliatePayout $payout): AffiliatePayout
    {
        if ($payout->status !== 'requested') {
            throw ValidationException::withMessages([
                'status' => ["Cannot approve a payout with status '{$payout->status}'."],
            ]);
        }

        $payout->update([
            'status' => 'approved',
        ]);

        return $payout->fresh(['affiliate', 'commissions']);
    }

    /**
     * Admin marks a payout as completed after sending funds.
     */
    public function completePayout(AffiliatePayout $payout, ?string $adminNote = null): AffiliatePayout
    {
        if (in_array($payout->status, ['completed', 'rejected'])) {
            throw ValidationException::withMessages([
                'status' => ["Cannot complete a payout that is already {$payout->status}."],
            ]);
        }

        $payout->update([
            'status'       => 'completed',
            'admin_note'   => $adminNote ?? $payout->admin_note,
            'processed_at' => now(),
        ]);

        return $payout->fresh(['affiliate', 'commissions']);
    }

    /**
     * Admin rejects a payout and returns linked commissions back to 'available'.
     */
    public function rejectPayout(AffiliatePayout $payout, ?string $adminNote = null): AffiliatePayout
    {
        if ($payout->status === 'rejected') {
            throw ValidationException::withMessages([
                'status' => ['This payout is already rejected.'],
            ]);
        }

        return DB::transaction(function () use ($payout, $adminNote) {
            $payout->update([
                'status'       => 'rejected',
                'admin_note'   => $adminNote ?? $payout->admin_note,
                'processed_at' => now(),
            ]);

            // Revert linked commissions back to available
            $commissionIds = $payout->commissions()->pluck('affiliate_commissions.id');
            if ($commissionIds->isNotEmpty()) {
                AffiliateCommission::whereIn('id', $commissionIds)
                    ->update(['status' => 'available']);
            }

            return $payout->fresh(['affiliate', 'commissions']);
        });
    }
}

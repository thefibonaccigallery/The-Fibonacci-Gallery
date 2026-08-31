<?php

namespace App\Http\Controllers;

use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Services\AffiliateService;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    public function __construct(private AffiliateService $affiliateService) {}

    /**
     * Get affiliate dashboard stats and summary.
     */
    public function dashboard(Request $request)
    {
        $summary = $this->affiliateService->getAffiliateSummary($request->user());

        return response()->json($summary);
    }

    /**
     * Get paginated commission history for the authenticated student.
     */
    public function commissions(Request $request)
    {
        $commissions = AffiliateCommission::with([
            'referredUser:id,uuid,name,email',
            'order:id,uuid,amount,created_at,course_id',
            'order.course:id,uuid,title',
        ])
            ->where('affiliate_id', $request->user()->id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($commissions);
    }

    /**
     * Get paginated payout history for the authenticated student.
     */
    public function payouts(Request $request)
    {
        $payouts = AffiliatePayout::withCount('commissions')
            ->where('affiliate_id', $request->user()->id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($payouts);
    }

    /**
     * Submit a payout request.
     */
    public function requestPayout(Request $request)
    {
        $request->validate([
            'wallet_address' => ['required', 'string', 'min:10', 'max:100'],
            'network'        => ['nullable', 'string', 'in:USDT (BEP20),USDT BEP20'],
        ]);

        $network = $request->network ?? 'USDT BEP20';
        $payout = $this->affiliateService->requestPayout(
            $request->user(),
            trim($request->wallet_address),
            $network
        );

        return response()->json([
            'success' => true,
            'message' => 'Payout request submitted successfully.',
            'payout'  => $payout,
        ], 201);
    }
}

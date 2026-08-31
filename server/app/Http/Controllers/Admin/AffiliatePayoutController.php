<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliatePayout;
use App\Services\AffiliateService;
use Illuminate\Http\Request;

class AffiliatePayoutController extends Controller
{
    public function __construct(private AffiliateService $affiliateService) {}

    /**
     * List all payout requests with filters and pagination.
     */
    public function index(Request $request)
    {
        $query = AffiliatePayout::with([
            'affiliate:id,uuid,name,email,country',
            'commissions.referredUser:id,name,email',
            'commissions.order:id,uuid,amount,course_id',
            'commissions.order.course:id,title',
        ])
            ->withCount('commissions')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('wallet_address', 'like', "%{$search}%")
                        ->orWhereHas('affiliate', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($query);
    }

    /**
     * Show single payout request details.
     */
    public function show(string $uuid)
    {
        $payout = AffiliatePayout::where('uuid', $uuid)
            ->with([
                'affiliate:id,uuid,name,email,country',
                'commissions.referredUser:id,name,email',
                'commissions.order:id,uuid,amount,course_id',
                'commissions.order.course:id,title',
            ])
            ->withCount('commissions')
            ->firstOrFail();

        return response()->json($payout);
    }

    /**
     * Approve a payout request.
     */
    public function approve(string $uuid)
    {
        $payout = AffiliatePayout::where('uuid', $uuid)->firstOrFail();
        $updated = $this->affiliateService->approvePayout($payout);

        return response()->json([
            'success' => true,
            'message' => 'Payout request approved successfully.',
            'payout'  => $updated,
        ]);
    }

    /**
     * Mark a payout request as completed (funds manually sent).
     */
    public function complete(Request $request, string $uuid)
    {
        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $payout = AffiliatePayout::where('uuid', $uuid)->firstOrFail();
        $updated = $this->affiliateService->completePayout($payout, $request->admin_note);

        return response()->json([
            'success' => true,
            'message' => 'Payout marked as completed successfully.',
            'payout'  => $updated,
        ]);
    }

    /**
     * Reject a payout request and return commissions back to available.
     */
    public function reject(Request $request, string $uuid)
    {
        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $payout = AffiliatePayout::where('uuid', $uuid)->firstOrFail();
        $updated = $this->affiliateService->rejectPayout($payout, $request->admin_note);

        return response()->json([
            'success' => true,
            'message' => 'Payout request rejected. Covered commissions have been reverted to available.',
            'payout'  => $updated,
        ]);
    }
}

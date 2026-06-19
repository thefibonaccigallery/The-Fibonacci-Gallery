<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    private const STATUSES = [
        'waiting',
        'confirming',
        'confirmed',
        'sending',
        'partially_paid',
        'finished',
        'failed',
        'expired',
        'refunded',
    ];

    public function index(Request $request)
    {
        $query = Order::with(['user', 'course'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->whereHas('user', fn($u) =>
                        $u->where('name', 'like', "%{$request->search}%")
                          ->orWhere('email', 'like', "%{$request->search}%"))
                      ->orWhereHas('course', fn($c) =>
                        $c->where('title', 'like', "%{$request->search}%"));
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($query);
    }

    public function update(Request $request, string $uuid)
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        $paidAt = $request->status === 'finished'
            ? ($order->paid_at ?? now())
            : $order->paid_at;

        $order->update([
            'status'  => $request->status,
            'paid_at' => $paidAt,
        ]);

        if ($request->status === 'finished') {
            Enrollment::firstOrCreate(
                ['user_id' => $order->user_id, 'course_id' => $order->course_id],
                ['enrolled_at' => now()]
            );
        }

        return response()->json([
            'message' => 'Order updated successfully',
            'order'   => $order->fresh(['user', 'course']),
        ]);
    }
}

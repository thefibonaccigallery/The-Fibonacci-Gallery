<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::with('course')
            ->withCount('enrollments')
            ->when($request->search, fn($q) =>
                $q->where('code', 'like', "%{$request->search}%"))
            ->when($request->filled('course_uuid'), fn($q) =>
                $q->whereHas('course', fn($c) =>
                    $c->where('uuid', $request->course_uuid)))
            ->when($request->filled('is_active'), fn($q) =>
                $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($query);
    }

    public function store(StoreCouponRequest $request)
    {
        $course = Course::where('uuid', $request->course_uuid)->firstOrFail();

        $coupon = Coupon::create([
            'course_id'           => $course->id,
            'code'                => strtoupper(trim($request->code)),
            'discount_percentage' => $request->discount_percentage,
            'max_uses'            => $request->max_uses,
            'expires_at'          => $request->expires_at,
            'is_active'           => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'message' => 'Coupon created successfully',
            'coupon'  => new CouponResource($coupon->load('course')),
        ], 201);
    }

    public function show(string $uuid)
    {
        $coupon = Coupon::where('uuid', $uuid)
            ->with('course')
            ->withCount('enrollments')
            ->firstOrFail();

        return response()->json(['coupon' => new CouponResource($coupon)]);
    }

    public function update(UpdateCouponRequest $request, string $uuid)
    {
        $coupon = Coupon::where('uuid', $uuid)->firstOrFail();

        if ($request->filled('course_uuid')) {
            $course            = Course::where('uuid', $request->course_uuid)->firstOrFail();
            $coupon->course_id = $course->id;
        }

        if ($request->has('code')) {
            $coupon->code = strtoupper(trim($request->code));
        }

        $coupon->fill($request->only([
            'discount_percentage', 'max_uses', 'expires_at', 'is_active',
        ]));

        $coupon->save();

        return response()->json([
            'message' => 'Coupon updated successfully',
            'coupon'  => new CouponResource($coupon->fresh(['course'])),
        ]);
    }

    public function destroy(string $uuid)
    {
        $coupon = Coupon::where('uuid', $uuid)->firstOrFail();
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully']);
    }

    public function toggle(string $uuid)
    {
        $coupon = Coupon::where('uuid', $uuid)->firstOrFail();
        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json([
            'message'   => 'Coupon status updated',
            'is_active' => $coupon->is_active,
        ]);
    }

    public function usage(Request $request, string $uuid)
    {
        $coupon = Coupon::where('uuid', $uuid)
            ->with('course')
            ->withCount('enrollments')
            ->firstOrFail();

        $enrollments = $coupon->enrollments()
            ->with('user:id,uuid,name,email')
            ->orderBy('enrolled_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'coupon'      => new CouponResource($coupon),
            'enrollments' => $enrollments,
        ]);
    }
}

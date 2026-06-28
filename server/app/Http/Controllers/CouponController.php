<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateCouponRequest;
use App\Models\Coupon;
use App\Models\Course;

class CouponController extends Controller
{
    public function validate(ValidateCouponRequest $request)
    {
        $course = Course::where('uuid', $request->course_uuid)
            ->where('is_published', true)
            ->firstOrFail();

        $coupon = Coupon::where('code', strtoupper(trim($request->code)))
            ->where('course_id', $course->id)
            ->first();

        if (!$coupon) {
            return response()->json([
                'valid'   => false,
                'message' => 'Invalid coupon code for this course.',
            ], 422);
        }

        if (!$coupon->is_active) {
            return response()->json([
                'valid'   => false,
                'message' => 'This coupon has been disabled.',
            ], 422);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json([
                'valid'   => false,
                'message' => 'This coupon has expired.',
            ], 422);
        }

        if ($coupon->max_uses !== null && $coupon->enrollments()->count() >= $coupon->max_uses) {
            return response()->json([
                'valid'   => false,
                'message' => 'This coupon has reached its usage limit.',
            ], 422);
        }

        $discountAmount = round($course->price * $coupon->discount_percentage / 100, 2);
        $finalPrice     = max(0, $course->price - $discountAmount);

        return response()->json([
            'valid'               => true,
            'coupon_uuid'         => $coupon->uuid,
            'code'                => $coupon->code,
            'discount_percentage' => (float) $coupon->discount_percentage,
            'discount_amount'     => $discountAmount,
            'final_price'         => $finalPrice,
            'is_free'             => $finalPrice == 0,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Enroll in a course or check if payment is required.
     *
     * POST /api/enroll/{uuid}
     */
    public function enroll(Request $request, $uuid)
    {
        $user   = auth()->user();
        $course = Course::where('uuid', $uuid)->firstOrFail();

        // Check already enrolled
        $alreadyEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json([
                'message' => 'You are already enrolled in this course.',
            ], 409);
        }

        // Free course — enroll immediately
        if ($course->price == 0.00) {
            Enrollment::create([
                'user_id'     => $user->id,
                'course_id'   => $course->id,
                'enrolled_at' => now(),
            ]);

            return response()->json([
                'requires_payment' => false,
                'message'          => 'Successfully enrolled.',
                'course_uuid'      => $course->uuid,
            ]);
        }

        // Paid course — return payment required response
        return response()->json([
            'requires_payment' => true,
            'course_uuid'      => $course->uuid,
            'course_title'     => $course->title,
            'amount'           => $course->price,
            'currency'         => 'USD',
        ]);
    }

    /**
     * Check the enrollment status for a course.
     *
     * GET /api/enroll/{uuid}/status
     */
    public function status($uuid)
    {
        $course     = Course::where('uuid', $uuid)->firstOrFail();
        $isEnrolled = Enrollment::where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->exists();

        return response()->json([
            'is_enrolled' => $isEnrolled,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEnrollmentRequest;
use App\Http\Resources\EnrollmentResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Enrollment::with(['user', 'course', 'coupon'])
            ->when($request->search, fn($q) => $q
                ->whereHas('user', fn($u) =>
                    $u->where('name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%"))
                ->orWhereHas('course', fn($c) =>
                    $c->where('title', 'like', "%{$request->search}%")))
            ->when($request->has('is_completed'), fn($q) =>
                $q->where('completed_at', $request->boolean('is_completed') ? '!=' : '=', null))
            ->orderBy('enrolled_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($query);
    }

    public function store(StoreEnrollmentRequest $request)
    {
        $user   = User::where('uuid', $request->user_uuid)->firstOrFail();
        $course = Course::where('uuid', $request->course_uuid)->firstOrFail();

        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['enrolled_at' => now()]
        );

        return response()->json([
            'message'    => 'Student enrolled successfully',
            'enrollment' => new EnrollmentResource($enrollment->load(['user', 'course'])),
        ], 201);
    }

    public function destroy(string $uuid)
    {
        $enrollment = Enrollment::where('uuid', $uuid)->firstOrFail();
        $enrollment->delete();

        return response()->json(['message' => 'Enrollment removed successfully']);
    }
}

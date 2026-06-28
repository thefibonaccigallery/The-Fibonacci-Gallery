<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnrollCourseRequest;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\VideoProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserCourseController extends Controller
{
    /**
     * Get all enrolled courses for the authenticated user
     */
    public function myEnrolledCourses(Request $request)
    {
        $user = Auth::user();

        $enrollments = Enrollment::where('user_id', $user->id)
            ->with(['course.chapters.videos' => function ($query) {
                $query->where('is_published', true)->orderBy('order');
            }])
            ->orderBy('enrolled_at', 'desc')
            ->get();

        $enrolledCourses = $enrollments->map(function ($enrollment) use ($user) {
            $course = $enrollment->course;

            // Get all video IDs for this course (internal only)
            $videoIds = $course->chapters->flatMap(function ($chapter) {
                return $chapter->videos->pluck('id');
            });

            // Get progress for all videos
            $completedVideos = VideoProgress::where('user_id', $user->id)
                ->whereIn('course_video_id', $videoIds)
                ->where('is_completed', true)
                ->count();

            $totalVideos = $videoIds->count();

            // Find the last watched video
            $lastProgress = VideoProgress::where('user_id', $user->id)
                ->whereIn('course_video_id', $videoIds)
                ->orderBy('last_watched_at', 'desc')
                ->first();

            $lastWatchedVideo = null;
            if ($lastProgress) {
                $lastWatchedVideo = $course->chapters
                    ->flatMap(fn($chapter) => $chapter->videos)
                    ->firstWhere('id', $lastProgress->course_video_id);
            }

            return [
                'uuid'        => $course->uuid,
                'title'       => $course->title,
                'description' => $course->description,
                'thumbnail_url' => $course->thumbnail_url,
                'level'       => $course->level,
                'totalLessons' => $totalVideos,
                'completedLessons' => $completedVideos,
                'progress_percentage' => $totalVideos > 0 ? round(($completedVideos / $totalVideos) * 100, 2) : 0,
                'enrolled_at' => $enrollment->enrolled_at,
                'last_watched_video' => $lastWatchedVideo ? [
                    'uuid'  => $lastWatchedVideo->uuid,
                    'title' => $lastWatchedVideo->title,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'courses' => $enrolledCourses,
        ]);
    }

    /**
     * Get courses available to explore (not enrolled)
     */
    public function exploreCourses(Request $request)
    {
        $user = Auth::user();

        // Get IDs of courses the user is already enrolled in
        $enrolledCourseIds = Enrollment::where('user_id', $user->id)
            ->pluck('course_id')
            ->toArray();

        // Get published courses that the user is not enrolled in
        $query = Course::where('is_published', true)
            ->whereNotIn('id', $enrolledCourseIds)
            ->with(['chapters.videos' => function ($query) {
                $query->where('is_published', true);
            }]);

        // Search filter
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Level filter
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $courses = $query->get();

        $exploreCourses = $courses->map(function ($course) {
            $totalVideos = $course->chapters->sum(function ($chapter) {
                return $chapter->videos->count();
            });

            return [
                'uuid'          => $course->uuid,
                'title'         => $course->title,
                'description'   => $course->description,
                'thumbnail_url' => $course->thumbnail_url,
                'level'         => $course->level,
                'price'         => $course->price,
                'totalLessons'  => $totalVideos,
                'length_seconds' => $course->length_seconds,
                'published_at'  => $course->published_at,
            ];
        });

        return response()->json([
            'success' => true,
            'courses' => $exploreCourses,
        ]);
    }

    /**
     * Get a single course detail for exploration
     */
    public function showCourse($uuid)
    {
        $user = Auth::user();

        $course = Course::where('is_published', true)
            ->with(['chapters' => function ($query) {
                $query->orderBy('order');
            }, 'chapters.videos' => function ($query) {
                $query->where('is_published', true)->orderBy('order');
            }])
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Check if user is enrolled
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $totalVideos = $course->chapters->sum(function ($chapter) {
            return $chapter->videos->count();
        });

        $courseData = [
            'uuid'          => $course->uuid,
            'title'         => $course->title,
            'description'   => $course->description,
            'thumbnail_url' => $course->thumbnail_url,
            'level'         => $course->level,
            'price'         => $course->price,
            'totalLessons'  => $totalVideos,
            'length_seconds' => $course->length_seconds,
            'published_at'  => $course->published_at,
            'what_is_included' => $course->what_is_included ?? [],
            'is_enrolled'   => $enrollment !== null,
            'chapters'      => $course->chapters->map(function ($chapter) {
                return [
                    'uuid'        => $chapter->uuid,
                    'title'       => $chapter->title,
                    'level'       => $chapter->level,
                    'order'       => $chapter->order,
                    'video_count' => $chapter->videos->count(),
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'course'  => $courseData,
        ]);
    }

    /**
     * Enroll in a course (free courses or 100% coupon codes)
     */
    public function enrollCourse(EnrollCourseRequest $request, $uuid)
    {
        $user = Auth::user();

        $course = Course::where('is_published', true)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $existingEnrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingEnrollment) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this course',
            ], 400);
        }

        $enrollmentData = [
            'user_id'     => $user->id,
            'course_id'   => $course->id,
            'enrolled_at' => now(),
            'type'        => 'free',
        ];

        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', strtoupper(trim($request->coupon_code)))
                ->where('course_id', $course->id)
                ->first();

            if (!$coupon || !$coupon->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired coupon code.',
                ], 422);
            }

            $finalPrice = max(0, $course->price - ($course->price * $coupon->discount_percentage / 100));

            if ($finalPrice > 0) {
                return response()->json([
                    'success'          => false,
                    'message'          => 'This coupon reduces the price but does not cover it fully. Please proceed to checkout.',
                    'requires_payment' => true,
                    'final_price'      => $finalPrice,
                ], 422);
            }

            $enrollmentData['type']      = 'coupon';
            $enrollmentData['coupon_id'] = $coupon->id;
        }

        $enrollment = Enrollment::create($enrollmentData);

        return response()->json([
            'success'    => true,
            'message'    => 'Successfully enrolled in the course',
            'enrollment' => $enrollment,
        ], 201);
    }

    /**
     * Get the course the user is currently learning (last watched)
     */
    public function continueLearningSuggestion()
    {
        $user = Auth::user();

        // Get the most recently watched video
        $lastProgress = VideoProgress::where('user_id', $user->id)
            ->orderBy('last_watched_at', 'desc')
            ->with('video.chapter.course')
            ->first();

        if (!$lastProgress || !$lastProgress->video) {
            // If no progress, get the first enrolled course
            $enrollment = Enrollment::where('user_id', $user->id)
                ->orderBy('enrolled_at', 'desc')
                ->with(['course.chapters.videos' => function ($query) {
                    $query->where('is_published', true)->orderBy('order');
                }])
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'success' => true,
                    'course'  => null,
                ]);
            }

            $course     = $enrollment->course;
            $videoIds   = $course->chapters->flatMap(fn($chapter) => $chapter->videos->pluck('id'));
            $totalVideos = $videoIds->count();

            return response()->json([
                'success' => true,
                'course'  => [
                    'uuid'           => $course->uuid,
                    'title'          => $course->title,
                    'description'    => $course->description,
                    'thumbnail_url'  => $course->thumbnail_url,
                    'totalLessons'   => $totalVideos,
                    'completedLessons' => 0,
                    'next_video_title' => $course->chapters->first()?->videos->first()?->title ?? 'Start Learning',
                ],
            ]);
        }

        $course = $lastProgress->video->chapter->course;

        $course->loadMissing([
            'chapters'        => fn($q) => $q->orderBy('order'),
            'chapters.videos' => fn($q) => $q->where('is_published', true)->orderBy('order'),
        ]);

        $videoIds = $course->chapters->flatMap(fn($chapter) => $chapter->videos->pluck('id'));

        $allProgress = VideoProgress::where('user_id', $user->id)
            ->whereIn('course_video_id', $videoIds)
            ->get()
            ->keyBy('course_video_id');

        $completedVideos = $allProgress->where('is_completed', true)->count();
        $totalVideos     = $videoIds->count();

        $nextVideo = null;
        foreach ($course->chapters as $chapter) {
            foreach ($chapter->videos as $video) {
                $prog = $allProgress->get($video->id);
                if (!$prog || !$prog->is_completed) {
                    $nextVideo = $video;
                    break 2;
                }
            }
        }

        return response()->json([
            'success' => true,
            'course'  => [
                'uuid'            => $course->uuid,
                'title'           => $course->title,
                'description'     => $course->description,
                'thumbnail_url'   => $course->thumbnail_url,
                'totalLessons'    => $totalVideos,
                'completedLessons' => $completedVideos,
                'next_video_title' => $nextVideo ? $nextVideo->title : 'Course Completed',
            ],
        ]);
    }
}

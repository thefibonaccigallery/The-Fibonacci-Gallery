<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseVideo;
use App\Models\VideoProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoursePlayerController extends Controller
{
    /**
     * Get course player data with chapters, videos, and progress
     */
    public function show($courseId)
    {
        $user = Auth::user();

        $course = Course::with([
            'chapters' => function ($query) {
                $query->orderBy('order');
            },
            'chapters.videos' => function ($query) {
                $query->where('is_published', true)->orderBy('order');
            },
        ])->findOrFail($courseId);

        // Get all video IDs for this course
        $videoIds = $course->chapters->flatMap(function ($chapter) {
            return $chapter->videos->pluck('id');
        });

        // Get progress for all videos in one query
        $progressData = VideoProgress::where('user_id', $user->id)
            ->whereIn('course_video_id', $videoIds)
            ->get()
            ->keyBy('course_video_id');

        // Attach progress to each video
        foreach ($course->chapters as $chapter) {
            foreach ($chapter->videos as $video) {
                $video->progress = $progressData->get($video->id);
            }
        }

        return response()->json([
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'thumbnail_url' => $course->thumbnail_url,
            ],
            'chapters' => $course->chapters->map(function ($chapter) {
                return [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'level' => $chapter->level,
                    'order' => $chapter->order,
                    'length_seconds' => $chapter->length_seconds,
                    'videos' => $chapter->videos->map(function ($video) {
                        return [
                            'id' => $video->id,
                            'title' => $video->title,
                            'youtube_video_id' => $video->youtube_video_id,
                            'level' => $video->level,
                            'order' => $video->order,
                            'duration_seconds' => $video->duration_seconds,
                            'progress' => $video->progress ? [
                                'watch_time_seconds' => $video->progress->watch_time_seconds,
                                'watch_percentage' => (float) $video->progress->watch_percentage,
                                'is_completed' => $video->progress->is_completed,
                                'last_watched_at' => $video->progress->last_watched_at,
                            ] : null,
                        ];
                    }),
                ];
            }),
        ]);
    }

    /**
     * Update video progress
     */
    public function updateProgress(Request $request, $videoId)
    {
        $request->validate([
            'watch_time_seconds' => 'required|integer|min:0',
            'watch_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $user = Auth::user();
        $video = CourseVideo::findOrFail($videoId);

        $watchPercentage = $request->watch_percentage;
        $isCompleted = $watchPercentage >= 70;

        $progress = VideoProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_video_id' => $video->id,
            ],
            [
                'watch_time_seconds' => $request->watch_time_seconds,
                'watch_percentage' => $watchPercentage,
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
                'last_watched_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'progress' => [
                'watch_time_seconds' => $progress->watch_time_seconds,
                'watch_percentage' => (float) $progress->watch_percentage,
                'is_completed' => $progress->is_completed,
                'last_watched_at' => $progress->last_watched_at,
            ],
        ]);
    }
}

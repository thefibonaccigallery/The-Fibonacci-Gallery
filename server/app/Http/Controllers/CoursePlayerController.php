<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProgressRequest;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\CourseVideo;
use App\Models\VideoProgress;
use Illuminate\Support\Facades\Auth;

class CoursePlayerController extends Controller
{
    public function show($uuid)
    {
        $user = Auth::user();

        $course = Course::with([
            'chapters' => fn($q) => $q->orderBy('order'),
            'chapters.videos' => fn($q) => $q->where('is_published', true)->orderBy('order'),
        ])->where('uuid', $uuid)->firstOrFail();

        $this->authorize('access', $course);

        $videoIds = $course->chapters->flatMap(fn($chapter) => $chapter->videos->pluck('id'));

        $progressData = VideoProgress::where('user_id', $user->id)
            ->whereIn('course_video_id', $videoIds)
            ->get()
            ->keyBy('course_video_id');

        foreach ($course->chapters as $chapter) {
            foreach ($chapter->videos as $video) {
                $video->progress = $progressData->get($video->id);
            }
        }

        return response()->json([
            'course' => [
                'uuid'          => $course->uuid,
                'title'         => $course->title,
                'description'   => $course->description,
                'thumbnail_url' => $course->thumbnail_url,
            ],
            'chapters' => $course->chapters->map(fn($chapter) => [
                'uuid'           => $chapter->uuid,
                'title'          => $chapter->title,
                'level'          => $chapter->level,
                'order'          => $chapter->order,
                'length_seconds' => $chapter->length_seconds,
                'videos'         => $chapter->videos->map(fn($video) => [
                    'uuid'             => $video->uuid,
                    'title'            => $video->title,
                    'youtube_video_id' => $video->youtube_video_id,
                    'level'            => $video->level,
                    'order'            => $video->order,
                    'duration_seconds' => $video->duration_seconds,
                    'cooldown_hours'   => $video->cooldown_hours,
                    'progress'         => $video->progress ? [
                        'watch_time_seconds' => $video->progress->watch_time_seconds,
                        'watch_percentage'   => (float) $video->progress->watch_percentage,
                        'is_completed'       => $video->progress->is_completed,
                        'last_watched_at'    => $video->progress->last_watched_at,
                        'cooldown_until'     => $video->progress->cooldown_until,
                        'is_in_cooldown'     => $video->progress->cooldown_until && now()->lt($video->progress->cooldown_until),
                    ] : null,
                ]),
            ]),
        ]);
    }

    public function updateProgress(UpdateProgressRequest $request, $uuid)
    {
        $user  = Auth::user();
        $video = CourseVideo::where('uuid', $uuid)->with('chapter.course')->firstOrFail();

        $this->authorize('access', $video->chapter->course);

        $existing = VideoProgress::where('user_id', $user->id)
            ->where('course_video_id', $video->id)
            ->first();

        $watchTimeSeconds = max($request->watch_time_seconds, $existing?->watch_time_seconds ?? 0);

        $watchPercentage = $video->duration_seconds > 0
            ? min(round(($watchTimeSeconds / $video->duration_seconds) * 100, 2), 100.0)
            : 0;

        $threshold        = config('types_course.completion_threshold', 70);
        $alreadyCompleted = $existing?->is_completed ?? false;
        $isNowCompleted   = $alreadyCompleted || ($watchPercentage >= $threshold);
        $isNewlyCompleted = !$alreadyCompleted && $isNowCompleted;

        $progress = VideoProgress::updateOrCreate(
            ['user_id' => $user->id, 'course_video_id' => $video->id],
            [
                'watch_time_seconds' => $watchTimeSeconds,
                'watch_percentage'   => $watchPercentage,
                'is_completed'       => $isNowCompleted,
                'completed_at'       => $isNowCompleted ? ($existing?->completed_at ?? now()) : null,
                'last_watched_at'    => now(),
            ]
        );

        if ($isNewlyCompleted) {
            $nextVideo = $this->getNextVideo($video);

            if ($nextVideo && $nextVideo->cooldown_hours > 0) {
                $nextProgress = VideoProgress::where('user_id', $user->id)
                    ->where('course_video_id', $nextVideo->id)
                    ->first();

                if (!$nextProgress || !$nextProgress->cooldown_until) {
                    VideoProgress::updateOrCreate(
                        ['user_id' => $user->id, 'course_video_id' => $nextVideo->id],
                        ['cooldown_until' => now()->addHours($nextVideo->cooldown_hours)],
                    );
                }
            }
        }

        return response()->json([
            'success'  => true,
            'progress' => [
                'watch_time_seconds' => $progress->watch_time_seconds,
                'watch_percentage'   => (float) $progress->watch_percentage,
                'is_completed'       => $progress->is_completed,
                'last_watched_at'    => $progress->last_watched_at,
                'cooldown_until'     => $progress->cooldown_until,
                'is_in_cooldown'     => $progress->cooldown_until && now()->lt($progress->cooldown_until),
            ],
        ]);
    }

    private function getNextVideo(CourseVideo $current): ?CourseVideo
    {
        $next = CourseVideo::where('chapter_id', $current->chapter_id)
            ->where('order', '>', $current->order)
            ->orderBy('order')
            ->first();

        if ($next) return $next;

        $nextChapter = Chapter::where('course_id', $current->chapter->course_id)
            ->where('order', '>', $current->chapter->order)
            ->orderBy('order')
            ->first();

        return $nextChapter?->videos()->orderBy('order')->first();
    }
}

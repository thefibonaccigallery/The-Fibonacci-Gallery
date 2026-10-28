<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProgressRequest;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\CourseVideo;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\VideoProgress;
use App\Services\CertificateService;
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

        $threshold = (float) config('types.course.completion_threshold', 70);

        // Auto-heal any records where is_completed was erroneously set below 70% threshold
        VideoProgress::where('user_id', $user->id)
            ->whereIn('course_video_id', $videoIds)
            ->where('is_completed', true)
            ->where('watch_percentage', '<', $threshold)
            ->update([
                'is_completed' => false,
                'completed_at' => null,
            ]);

        $progressData = VideoProgress::where('user_id', $user->id)
            ->whereIn('course_video_id', $videoIds)
            ->get()
            ->keyBy('course_video_id');

        foreach ($course->chapters as $chapter) {
            foreach ($chapter->videos as $video) {
                $video->progress = $progressData->get($video->id);
            }
        }

        $certificate = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $certificateData = null;
        if ($certificate) {
            $certificateService = app(CertificateService::class);
            $certificateData = [
                'uuid'               => $certificate->uuid,
                'certificate_number' => $certificate->certificate_number,
                'course_title'       => $certificate->course_title,
                'recipient_name'     => $certificate->recipient_name,
                'issued_at'          => $certificate->issued_at,
                'verify_url'         => $certificateService->getVerificationUrl($certificate),
            ];
        }

        return response()->json([
            'certificate' => $certificateData,
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
                    'level'            => $video->level,
                    'order'            => $video->order,
                    'duration_seconds' => $video->duration_seconds,
                    'cooldown_hours'   => $video->cooldown_hours,
                    'is_big_five'      => (bool) $video->is_big_five,
                    'isBigFive'        => (bool) $video->is_big_five,
                    'progress'         => $video->progress ? [
                        'watch_time_seconds' => $video->progress->watch_time_seconds,
                        'watch_percentage'   => (float) $video->progress->watch_percentage,
                        'is_completed'       => (bool) ($video->progress->is_completed && ((float) $video->progress->watch_percentage >= $threshold)),
                        'last_watched_at'    => $video->progress->last_watched_at,
                        'cooldown_until'     => $video->progress->cooldown_until,
                        'is_in_cooldown'     => $video->progress->cooldown_until && now()->lt($video->progress->cooldown_until),
                    ] : null,
                ]),
            ]),
        ]);
    }

    public function getVideoToken($uuid)
    {
        $video = CourseVideo::where('uuid', $uuid)->with('chapter.course')->firstOrFail();
        $this->authorize('access', $video->chapter->course);

        $user = Auth::user();
        if ($user && !$this->isVideoUnlockedForUser($user, $video)) {
            return response()->json([
                'message' => 'Video is locked. You must watch at least 70% of the previous video to unlock.',
            ], 403);
        }

        if (!$video->youtube_video_id) {
            return response()->json(['stream_token' => null]);
        }

        // Obfuscate YouTube video ID in transit
        $obfuscated = base64_encode(str_rot13($video->youtube_video_id));

        return response()->json([
            'stream_token' => $obfuscated,
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

        $threshold        = (float) config('types.course.completion_threshold', 70);
        $alreadyCompleted = ($existing?->is_completed ?? false) && (((float) ($existing?->watch_percentage ?? 0)) >= $threshold);
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

        $course = $video->chapter->course;

        // Check overall course completion across all published videos
        $allPublishedVideoIds = CourseVideo::whereHas('chapter', function ($q) use ($course) {
            $q->where('course_id', $course->id);
        })->where('is_published', true)->pluck('id');

        $totalCourseVideos = $allPublishedVideoIds->count();
        $completedVideosCount = VideoProgress::where('user_id', $user->id)
            ->whereIn('course_video_id', $allPublishedVideoIds)
            ->where('is_completed', true)
            ->count();

        $isCourseCompleted = ($totalCourseVideos > 0) && ($completedVideosCount >= $totalCourseVideos);
        $certificateData = null;

        if ($isCourseCompleted) {
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($enrollment) {
                $enrollment->update([
                    'progress_percentage' => 100.0,
                    'completed_at'        => $enrollment->completed_at ?? now(),
                ]);

                $certificateService = app(CertificateService::class);
                $certificate = $certificateService->issueCertificate($user, $course, $enrollment);
                $certificateData = [
                    'uuid'               => $certificate->uuid,
                    'certificate_number' => $certificate->certificate_number,
                    'course_title'       => $certificate->course_title,
                    'recipient_name'     => $certificate->recipient_name,
                    'issued_at'          => $certificate->issued_at,
                    'verify_url'         => $certificateService->getVerificationUrl($certificate),
                ];
            }
        }

        return response()->json([
            'success'          => true,
            'course_completed' => $isCourseCompleted,
            'certificate'      => $certificateData,
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

    private function isVideoUnlockedForUser(User $user, CourseVideo $video): bool
    {
        $threshold = (float) config('types.course.completion_threshold', 70);

        // If user already completed or reached threshold on this video, allow access
        $currentProgress = VideoProgress::where('user_id', $user->id)
            ->where('course_video_id', $video->id)
            ->first();

        if ($currentProgress && ($currentProgress->is_completed || (float) $currentProgress->watch_percentage >= $threshold)) {
            return true;
        }

        // If video is in active cooldown, lock it
        if ($currentProgress && $currentProgress->cooldown_until && now()->lt($currentProgress->cooldown_until)) {
            return false;
        }

        // Find previous video in the current chapter
        $previousVideo = CourseVideo::where('chapter_id', $video->chapter_id)
            ->where('is_published', true)
            ->where('order', '<', $video->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($previousVideo) {
            $prevProgress = VideoProgress::where('user_id', $user->id)
                ->where('course_video_id', $previousVideo->id)
                ->first();

            return (bool) ($prevProgress && $prevProgress->is_completed && ((float) $prevProgress->watch_percentage >= $threshold));
        }

        // It is the first video of this chapter. Check previous chapter
        $currentChapter = $video->chapter;
        $previousChapter = Chapter::where('course_id', $currentChapter->course_id)
            ->where('order', '<', $currentChapter->order)
            ->orderBy('order', 'desc')
            ->first();

        // If there is no previous chapter, this is the very first video of the course -> Unlocked
        if (!$previousChapter) {
            return true;
        }

        // All published videos in the previous chapter must be completed (>= threshold)
        $prevChapterVideoIds = $previousChapter->videos()->where('is_published', true)->pluck('id');
        if ($prevChapterVideoIds->isEmpty()) {
            return true;
        }

        $completedCount = VideoProgress::where('user_id', $user->id)
            ->whereIn('course_video_id', $prevChapterVideoIds)
            ->where('is_completed', true)
            ->where('watch_percentage', '>=', $threshold)
            ->count();

        return $completedCount >= $prevChapterVideoIds->count();
    }
}

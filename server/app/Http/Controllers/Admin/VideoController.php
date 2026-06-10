<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseVideo;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseVideo::with('chapter.course');

        // Filter by course
        if ($request->has('course_uuid')) {
            $query->whereHas('chapter.course', function ($q) use ($request) {
                $q->where('uuid', $request->course_uuid);
            });
        }

        // Filter by chapter
        if ($request->has('chapter_uuid')) {
            $query->whereHas('chapter', function ($q) use ($request) {
                $q->where('uuid', $request->chapter_uuid);
            });
        }

        // Filter by published status
        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $videos = $query->paginate($request->get('per_page', 15));

        return response()->json($videos);
    }

    public function store(Request $request)
    {
        $chapter = Chapter::findByUuidOrFail($request->chapter_uuid);
        $request->merge(['chapter_id' => $chapter->id]);

        $validated = $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'title' => 'required|string|max:255',
            'youtube_video_id' => 'nullable|string|max:20',
            'level' => ['nullable', Rule::in(config('types_course.levels'))],
            'order' => 'nullable|integer|min:1',
            'duration_seconds' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
            'is_preview' => 'boolean',
        ]);

        $video = CourseVideo::create($validated);

        return response()->json([
            'message' => 'Video created successfully',
            'video' => $video->load('chapter.course'),
        ], 201);
    }

    public function show($uuid)
    {
        $video = CourseVideo::with('chapter.course')->where('uuid', $uuid)->firstOrFail();
        return response()->json($video);
    }

    public function update(Request $request, $uuid)
    {
        $video = CourseVideo::findByUuidOrFail($uuid);

        if ($request->has('chapter_uuid')) {
            $chapter = Chapter::findByUuidOrFail($request->chapter_uuid);
            $request->merge(['chapter_id' => $chapter->id]);
        }

        $validated = $request->validate([
            'chapter_id' => 'sometimes|required|exists:chapters,id',
            'title' => 'sometimes|required|string|max:255',
            'youtube_video_id' => 'nullable|string|max:20',
            'level' => ['nullable', Rule::in(config('types_course.levels'))],
            'order' => 'sometimes|required|integer|min:1',
            'duration_seconds' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
            'is_preview' => 'boolean',
        ]);

        $video->update($validated);

        return response()->json([
            'message' => 'Video updated successfully',
            'video' => $video->load('chapter.course'),
        ]);
    }

    public function destroy($uuid)
    {
        $video = CourseVideo::findByUuidOrFail($uuid);

        // Delete video file if exists (implement based on your storage strategy)
        // Storage::disk('public')->delete('videos/' . $video->uuid . '.mp4');

        $video->delete();

        return response()->json([
            'message' => 'Video deleted successfully',
        ]);
    }

    public function uploadVideo(Request $request, $uuid)
    {
        $video = CourseVideo::findByUuidOrFail($uuid);

        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi,wmv|max:512000', // 500MB max
        ]);

        // Store video file
        $file = $request->file('video');
        $filename = $video->uuid . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('videos', $filename, 'public');

        // You might want to process the video here (e.g., get duration, create thumbnails)
        // For now, we'll just store the path

        return response()->json([
            'message' => 'Video uploaded successfully',
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'videos' => 'required|array',
            'videos.*.uuid' => 'required|exists:course_videos,uuid',
            'videos.*.order' => 'required|integer|min:1',
        ]);

        foreach ($validated['videos'] as $videoData) {
            CourseVideo::where('uuid', $videoData['uuid'])->update(['order' => $videoData['order']]);
        }

        return response()->json([
            'message' => 'Videos reordered successfully',
        ]);
    }
}

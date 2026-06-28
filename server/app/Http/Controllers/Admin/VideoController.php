<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVideoRequest;
use App\Http\Requests\Admin\UpdateVideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\CourseVideo;
use App\Models\Chapter;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseVideo::with('chapter.course');

        if ($request->has('course_uuid')) {
            $query->whereHas('chapter.course', fn($q) => $q->where('uuid', $request->course_uuid));
        }

        if ($request->has('chapter_uuid')) {
            $query->whereHas('chapter', fn($q) => $q->where('uuid', $request->chapter_uuid));
        }

        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $sortBy    = $request->get('sort_by', 'order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        return response()->json($query->paginate($request->get('per_page', 15)));
    }

    public function store(StoreVideoRequest $request)
    {
        $video = CourseVideo::create($request->validated());

        return response()->json([
            'message' => 'Video created successfully',
            'video'   => new VideoResource($video->load('chapter.course')),
        ], 201);
    }

    public function show($uuid)
    {
        $video = CourseVideo::with('chapter.course')->where('uuid', $uuid)->firstOrFail();

        return response()->json(new VideoResource($video));
    }

    public function update(UpdateVideoRequest $request, $uuid)
    {
        $video = CourseVideo::findByUuidOrFail($uuid);
        $video->update($request->validated());

        return response()->json([
            'message' => 'Video updated successfully',
            'video'   => new VideoResource($video->load('chapter.course')),
        ]);
    }

    public function destroy($uuid)
    {
        $video = CourseVideo::findByUuidOrFail($uuid);
        $video->delete();

        return response()->json(['message' => 'Video deleted successfully']);
    }

    public function uploadVideo(Request $request, $uuid)
    {
        $video = CourseVideo::findByUuidOrFail($uuid);

        $request->validate([
            'video' => ['required', 'file', 'mimes:mp4,mov,avi,wmv', 'max:512000'],
        ]);

        $file     = $request->file('video');
        $filename = $video->uuid . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('videos', $filename, 'public');

        return response()->json([
            'message' => 'Video uploaded successfully',
            'path'    => $path,
            'url'     => asset('storage/' . $path),
        ]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'videos'         => ['required', 'array'],
            'videos.*.uuid'  => ['required', 'exists:course_videos,uuid'],
            'videos.*.order' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated['videos'] as $videoData) {
            CourseVideo::where('uuid', $videoData['uuid'])->update(['order' => $videoData['order']]);
        }

        return response()->json(['message' => 'Videos reordered successfully']);
    }
}

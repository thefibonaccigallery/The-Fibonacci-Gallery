<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with('chapters.videos');

        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        return response()->json($query->paginate($request->get('per_page', 15)));
    }

    public function store(StoreCourseRequest $request)
    {
        $validated = $request->validated();

        if (!empty($validated['is_published'])) {
            $validated['published_at'] = now();
        }

        $course = Course::create($validated);

        return response()->json([
            'message' => 'Course created successfully',
            'course'  => new CourseResource($course->load('chapters.videos')),
        ], 201);
    }

    public function show($uuid)
    {
        $course = Course::with('chapters.videos')->where('uuid', $uuid)->firstOrFail();

        return response()->json(new CourseResource($course));
    }

    public function update(UpdateCourseRequest $request, $uuid)
    {
        $course    = Course::findByUuidOrFail($uuid);
        $validated = $request->validated();

        if (isset($validated['is_published']) && $validated['is_published'] && !$course->is_published) {
            $validated['published_at'] = now();
        }

        $course->update($validated);

        return response()->json([
            'message' => 'Course updated successfully',
            'course'  => new CourseResource($course->load('chapters.videos')),
        ]);
    }

    public function destroy($uuid)
    {
        $course = Course::findByUuidOrFail($uuid);

        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    public function uploadThumbnail(Request $request, $uuid)
    {
        $course = Course::findByUuidOrFail($uuid);

        $request->validate([
            'thumbnail' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $file     = $request->file('thumbnail');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('thumbnails', $file, $filename);

        $course->update(['thumbnail' => 'thumbnails/' . $filename]);

        return response()->json([
            'message'       => 'Thumbnail uploaded successfully',
            'thumbnail_url' => $course->thumbnail_url,
        ]);
    }

    public function deleteThumbnail($uuid)
    {
        $course = Course::findByUuidOrFail($uuid);

        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
            $course->update(['thumbnail' => null]);
        }

        return response()->json(['message' => 'Thumbnail deleted successfully']);
    }
}

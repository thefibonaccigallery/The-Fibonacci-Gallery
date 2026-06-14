<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with('chapters.videos');

        // Filter by published status
        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $courses = $query->paginate($request->get('per_page', 15));

        return response()->json($courses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:courses,slug',
            'description' => 'nullable|string',
            'level' => ['required', Rule::in(config('types_course.levels'))],
            'price' => 'required|numeric|min:0',
            'is_published' => 'boolean',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Set published_at if publishing
        if (!empty($validated['is_published']) && $validated['is_published']) {
            $validated['published_at'] = now();
        }

        $course = Course::create($validated);

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course->load('chapters.videos'),
        ], 201);
    }

    public function show($uuid)
    {
        $course = Course::with('chapters.videos')->where('uuid', $uuid)->firstOrFail();
        return response()->json($course);
    }

    public function update(Request $request, $uuid)
    {
        $course = Course::findByUuidOrFail($uuid);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => ['sometimes', 'required', 'string', Rule::unique('courses', 'slug')->ignore($course->id)],
            'description' => 'nullable|string',
            'level' => ['sometimes', 'required', Rule::in(config('types/course.levels'))],
            'price' => 'sometimes|required|numeric|min:0',
            'is_published' => 'boolean',
        ]);

        // Set published_at when publishing for the first time
        if (isset($validated['is_published']) && $validated['is_published'] && !$course->is_published) {
            $validated['published_at'] = now();
        }

        $course->update($validated);

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course->load('chapters.videos'),
        ]);
    }

    public function destroy($uuid)
    {
        $course = Course::findByUuidOrFail($uuid);

        // Delete thumbnail if exists
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully',
        ]);
    }

    public function uploadThumbnail(Request $request, $uuid)
    {
        $course = Course::findByUuidOrFail($uuid);

        $request->validate([
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Delete old thumbnail if exists
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        // Store new thumbnail
        $path = $request->file('thumbnail')->store('thumbnails', 'public');

        $course->update(['thumbnail' => $path]);

        return response()->json([
            'message' => 'Thumbnail uploaded successfully',
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

        return response()->json([
            'message' => 'Thumbnail deleted successfully',
        ]);
    }
}
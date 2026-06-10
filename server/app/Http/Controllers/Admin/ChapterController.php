<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChapterController extends Controller
{
    public function index(Request $request)
    {
        $query = Chapter::with('course', 'videos');

        // Filter by course
        if ($request->has('course_uuid')) {
            $course = Course::findByUuidOrFail($request->course_uuid);
            $query->where('course_id', $course->id);
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $chapters = $query->paginate($request->get('per_page', 15));

        return response()->json($chapters);
    }

    public function store(Request $request)
    {
        $course = Course::findByUuidOrFail($request->course_uuid);
        $request->merge(['course_id' => $course->id]);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'level' => ['nullable', Rule::in(config('types_course.levels'))],
            'order' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('chapters')->where(function ($query) use ($course) {
                    return $query->where('course_id', $course->id);
                }),
            ],
        ], [
            'order.unique' => 'This order number is already used in this course. Please choose a different order.',
        ]);

        $chapter = Chapter::create($validated);

        return response()->json([
            'message' => 'Chapter created successfully',
            'chapter' => $chapter->load('course', 'videos'),
        ], 201);
    }

    public function show($uuid)
    {
        $chapter = Chapter::with('course', 'videos')->where('uuid', $uuid)->firstOrFail();
        return response()->json($chapter);
    }

    public function update(Request $request, $uuid)
    {
        $chapter = Chapter::findByUuidOrFail($uuid);

        if ($request->has('course_uuid')) {
            $course = Course::findByUuidOrFail($request->course_uuid);
            $request->merge(['course_id' => $course->id]);
        }

        $validated = $request->validate([
            'course_id' => 'sometimes|required|exists:courses,id',
            'title' => 'sometimes|required|string|max:255',
            'level' => ['nullable', Rule::in(config('types/course.levels'))],
            'order' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                Rule::unique('chapters')->where(function ($query) use ($request, $chapter) {
                    $courseId = $request->course_id ?? $chapter->course_id;
                    return $query->where('course_id', $courseId);
                })->ignore($chapter->id),
            ],
        ], [
            'order.unique' => 'This order number is already used in this course. Please choose a different order.',
        ]);

        $chapter->update($validated);

        return response()->json([
            'message' => 'Chapter updated successfully',
            'chapter' => $chapter->load('course', 'videos'),
        ]);
    }

    public function destroy($uuid)
    {
        $chapter = Chapter::findByUuidOrFail($uuid);
        $chapter->delete();

        return response()->json([
            'message' => 'Chapter deleted successfully',
        ]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'chapters' => 'required|array',
            'chapters.*.uuid' => 'required|exists:chapters,uuid',
            'chapters.*.order' => 'required|integer|min:1',
        ]);

        foreach ($validated['chapters'] as $chapterData) {
            Chapter::where('uuid', $chapterData['uuid'])->update(['order' => $chapterData['order']]);
        }

        return response()->json([
            'message' => 'Chapters reordered successfully',
        ]);
    }
}

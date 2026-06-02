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
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
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
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'level' => ['nullable', Rule::in(config('types_course.levels'))],
            'order' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('chapters')->where(function ($query) use ($request) {
                    return $query->where('course_id', $request->course_id);
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

    public function show($id)
    {
        $chapter = Chapter::with('course', 'videos')->findOrFail($id);
        return response()->json($chapter);
    }

    public function update(Request $request, $id)
    {
        $chapter = Chapter::findOrFail($id);

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

    public function destroy($id)
    {
        $chapter = Chapter::findOrFail($id);
        $chapter->delete();

        return response()->json([
            'message' => 'Chapter deleted successfully',
        ]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'chapters' => 'required|array',
            'chapters.*.id' => 'required|exists:chapters,id',
            'chapters.*.order' => 'required|integer|min:1',
        ]);

        foreach ($validated['chapters'] as $chapterData) {
            Chapter::where('id', $chapterData['id'])->update(['order' => $chapterData['order']]);
        }

        return response()->json([
            'message' => 'Chapters reordered successfully',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreChapterRequest;
use App\Http\Requests\Admin\UpdateChapterRequest;
use App\Http\Resources\ChapterResource;
use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function index(Request $request)
    {
        $query = Chapter::with('course', 'videos');

        if ($request->has('course_uuid')) {
            $course = Course::findByUuidOrFail($request->course_uuid);
            $query->where('course_id', $course->id);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $sortBy    = $request->get('sort_by', 'order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        return response()->json($query->paginate($request->get('per_page', 15)));
    }

    public function store(StoreChapterRequest $request)
    {
        $chapter = Chapter::create($request->validated());

        return response()->json([
            'message' => 'Chapter created successfully',
            'chapter' => new ChapterResource($chapter->load('course', 'videos')),
        ], 201);
    }

    public function show($uuid)
    {
        $chapter = Chapter::with('course', 'videos')->where('uuid', $uuid)->firstOrFail();

        return response()->json(new ChapterResource($chapter));
    }

    public function update(UpdateChapterRequest $request, $uuid)
    {
        $chapter = Chapter::findByUuidOrFail($uuid);
        $chapter->update($request->validated());

        return response()->json([
            'message' => 'Chapter updated successfully',
            'chapter' => new ChapterResource($chapter->load('course', 'videos')),
        ]);
    }

    public function destroy($uuid)
    {
        $chapter = Chapter::findByUuidOrFail($uuid);
        $chapter->delete();

        return response()->json(['message' => 'Chapter deleted successfully']);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'chapters'         => ['required', 'array'],
            'chapters.*.uuid'  => ['required', 'exists:chapters,uuid'],
            'chapters.*.order' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated['chapters'] as $chapterData) {
            Chapter::where('uuid', $chapterData['uuid'])->update(['order' => $chapterData['order']]);
        }

        return response()->json(['message' => 'Chapters reordered successfully']);
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        if ($this->has('course_uuid')) {
            $course = Course::findByUuidOrFail($this->course_uuid);
            $this->merge(['course_id' => $course->id]);
        }
    }

    public function rules(): array
    {
        $chapter  = Chapter::where('uuid', $this->route('uuid'))->firstOrFail();
        $courseId = $this->input('course_id', $chapter->course_id);

        return [
            'course_id' => ['sometimes', 'required', 'exists:courses,id'],
            'title'     => ['sometimes', 'required', 'string', 'max:255'],
            'level'     => ['nullable', Rule::in(config('types_course.levels'))],
            'order'     => [
                'sometimes', 'required', 'integer', 'min:1',
                Rule::unique('chapters')
                    ->where(fn($q) => $q->where('course_id', $courseId))
                    ->ignore($chapter->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'order.unique' => 'This order number is already used in this course. Please choose a different order.',
        ];
    }
}

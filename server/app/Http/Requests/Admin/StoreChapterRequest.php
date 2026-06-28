<?php

namespace App\Http\Requests\Admin;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChapterRequest extends FormRequest
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
        $courseId = $this->input('course_id');

        return [
            'course_id' => ['required', 'exists:courses,id'],
            'title'     => ['required', 'string', 'max:255'],
            'level'     => ['nullable', Rule::in(config('types_course.levels'))],
            'order'     => [
                'nullable', 'integer', 'min:1',
                Rule::unique('chapters')->where(fn($q) => $q->where('course_id', $courseId)),
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

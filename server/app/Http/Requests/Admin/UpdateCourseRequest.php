<?php

namespace App\Http\Requests\Admin;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseId = Course::where('uuid', $this->route('uuid'))->value('id');

        return [
            'title'              => ['sometimes', 'required', 'string', 'max:255'],
            'slug'               => ['sometimes', 'required', 'string', Rule::unique('courses', 'slug')->ignore($courseId)],
            'description'        => ['nullable', 'string'],
            'level'              => ['sometimes', 'required', Rule::in(config('types_course.levels'))],
            'price'              => ['sometimes', 'required', 'numeric', 'min:0'],
            'what_is_included'   => ['nullable', 'array'],
            'what_is_included.*' => ['string', 'max:255'],
            'is_published'       => ['boolean'],
        ];
    }
}

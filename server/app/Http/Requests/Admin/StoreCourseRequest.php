<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'              => ['required', 'string', 'max:255'],
            'slug'               => ['nullable', 'string', 'unique:courses,slug'],
            'description'        => ['nullable', 'string'],
            'level'              => ['required', Rule::in(config('types_course.levels'))],
            'price'              => ['required', 'numeric', 'min:0'],
            'what_is_included'   => ['nullable', 'array'],
            'what_is_included.*' => ['string', 'max:255'],
            'is_published'       => ['boolean'],
        ];
    }
}

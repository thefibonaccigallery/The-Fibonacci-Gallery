<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_uuid'   => ['required', 'uuid', 'exists:users,uuid'],
            'course_uuid' => ['required', 'uuid', 'exists:courses,uuid'],
        ];
    }
}

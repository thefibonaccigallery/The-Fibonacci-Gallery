<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_uuid'         => ['required', 'string', 'exists:courses,uuid'],
            'code'                => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'discount_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            'max_uses'            => ['nullable', 'integer', 'min:1'],
            'expires_at'          => ['nullable', 'date', 'after:today'],
            'is_active'           => ['boolean'],
        ];
    }
}

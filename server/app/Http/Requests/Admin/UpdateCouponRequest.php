<?php

namespace App\Http\Requests\Admin;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $couponId = Coupon::where('uuid', $this->route('uuid'))->value('id');

        return [
            'course_uuid'         => ['sometimes', 'string', 'exists:courses,uuid'],
            'code'                => ['sometimes', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($couponId)],
            'discount_percentage' => ['sometimes', 'numeric', 'min:1', 'max:100'],
            'max_uses'            => ['nullable', 'integer', 'min:1'],
            'expires_at'          => ['nullable', 'date'],
            'is_active'           => ['sometimes', 'boolean'],
        ];
    }
}

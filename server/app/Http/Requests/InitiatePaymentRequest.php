<?php

namespace App\Http\Requests;

use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isFree = false;

        $course = Course::where('uuid', $this->route('uuid'))->first();
        if ($course) {
            $amount = (float) $course->price;

            if ($this->filled('coupon_code')) {
                $coupon = Coupon::where('code', strtoupper(trim((string) $this->coupon_code)))
                    ->where('course_id', $course->id)
                    ->first();

                if ($coupon && $coupon->isValid()) {
                    $discount = $amount * $coupon->discount_percentage / 100;
                    $amount = max(0, $amount - $discount);
                }
            }

            if ($amount == 0) {
                $isFree = true;
            }
        }

        if ($isFree) {
            return [
                'pay_currency' => ['nullable', 'string'],
                'coupon_code'  => ['nullable', 'string'],
            ];
        }

        return [
            'pay_currency' => [
                'required',
                'string',
                Rule::in(array_column(config('types.order.accepted_currencies'), 'id')),
            ],
            'coupon_code'  => ['nullable', 'string'],
        ];
    }
}

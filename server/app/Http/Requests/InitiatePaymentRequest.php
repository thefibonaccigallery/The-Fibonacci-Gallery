<?php

namespace App\Http\Requests;

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

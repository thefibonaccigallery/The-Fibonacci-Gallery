<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pay_currency' => ['required', 'string'],
            'coupon_code'  => ['nullable', 'string'],
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'          => $this->uuid,
            'amount'        => (float) $this->amount,
            'currency'      => $this->currency,
            'pay_currency'  => $this->pay_currency,
            'pay_address'   => $this->pay_address,
            'pay_amount'    => (float) $this->pay_amount,
            'actually_paid' => (float) $this->actually_paid,
            'status'        => $this->status,
            'paid_at'       => $this->paid_at?->toIso8601String(),
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),
            'user'          => new UserResource($this->whenLoaded('user')),
            'course'        => new CourseResource($this->whenLoaded('course')),
            'coupon'        => new CouponResource($this->whenLoaded('coupon')),
        ];
    }
}

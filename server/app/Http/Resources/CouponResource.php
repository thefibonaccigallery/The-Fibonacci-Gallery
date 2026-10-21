<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'                => $this->uuid,
            'code'                => $this->code,
            'discount_percentage' => (float) $this->discount_percentage,
            'max_uses'            => $this->max_uses,
            'expires_at'          => $this->expires_at?->toIso8601String(),
            'is_active'           => $this->is_active,
            'show_on_homepage'    => $this->show_on_homepage,
            'enrollments_count'   => $this->whenCounted('enrollments'),
            'created_at'          => $this->created_at?->toIso8601String(),
            'updated_at'          => $this->updated_at?->toIso8601String(),
            'course'              => new CourseResource($this->whenLoaded('course')),
        ];
    }
}

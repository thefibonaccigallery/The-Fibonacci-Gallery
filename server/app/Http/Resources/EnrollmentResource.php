<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'                => $this->uuid,
            'type'                => $this->type,
            'enrolled_at'         => $this->enrolled_at?->toIso8601String(),
            'completed_at'        => $this->completed_at?->toIso8601String(),
            'progress_percentage' => (float) $this->progress_percentage,
            'user'                => new UserResource($this->whenLoaded('user')),
            'course'              => new CourseResource($this->whenLoaded('course')),
            'coupon'              => new CouponResource($this->whenLoaded('coupon')),
        ];
    }
}

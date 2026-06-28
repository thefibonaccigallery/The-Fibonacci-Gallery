<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChapterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'           => $this->uuid,
            'title'          => $this->title,
            'level'          => $this->level,
            'order'          => $this->order,
            'length_seconds' => $this->length_seconds,
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
            'course'         => new CourseResource($this->whenLoaded('course')),
            'videos'         => VideoResource::collection($this->whenLoaded('videos')),
        ];
    }
}

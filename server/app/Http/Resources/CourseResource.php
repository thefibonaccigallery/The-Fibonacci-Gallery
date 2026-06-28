<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'             => $this->uuid,
            'title'            => $this->title,
            'slug'             => $this->slug,
            'description'      => $this->description,
            'thumbnail'        => $this->thumbnail,
            'thumbnail_url'    => $this->thumbnail_url,
            'level'            => $this->level,
            'price'            => (float) $this->price,
            'length_seconds'   => $this->length_seconds,
            'what_is_included' => $this->what_is_included ?? [],
            'is_published'     => $this->is_published,
            'published_at'     => $this->published_at?->toIso8601String(),
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
            'chapters'         => ChapterResource::collection($this->whenLoaded('chapters')),
        ];
    }
}

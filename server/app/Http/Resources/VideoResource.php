<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'             => $this->uuid,
            'title'            => $this->title,
            'youtube_video_id' => $this->youtube_video_id,
            'level'            => $this->level,
            'order'            => $this->order,
            'duration_seconds' => $this->duration_seconds,
            'cooldown_hours'   => $this->cooldown_hours,
            'is_published'     => $this->is_published,
            'is_preview'       => $this->is_preview,
            'is_big_five'      => (bool) $this->is_big_five,
            'isBigFive'        => (bool) $this->is_big_five,
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
            'chapter'          => new ChapterResource($this->whenLoaded('chapter')),
        ];
    }
}

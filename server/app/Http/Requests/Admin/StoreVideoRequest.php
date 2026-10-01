<?php

namespace App\Http\Requests\Admin;

use App\Models\Chapter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        if ($this->has('chapter_uuid')) {
            $chapter = Chapter::findByUuidOrFail($this->chapter_uuid);
            $this->merge(['chapter_id' => $chapter->id]);
        }

        if ($this->has('isBigFive') && !$this->has('is_big_five')) {
            $this->merge(['is_big_five' => $this->boolean('isBigFive')]);
        }
    }

    public function rules(): array
    {
        return [
            'chapter_id'       => ['required', 'exists:chapters,id'],
            'title'            => ['required', 'string', 'max:255'],
            'youtube_video_id' => ['nullable', 'string', 'max:20'],
            'level'            => ['nullable', Rule::in(config('types_course.levels'))],
            'order'            => ['nullable', 'integer', 'min:1'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'cooldown_hours'   => ['nullable', 'integer', 'min:0', 'max:168'],
            'is_published'     => ['boolean'],
            'is_preview'       => ['boolean'],
            'is_big_five'      => ['nullable', 'boolean'],
            'isBigFive'        => ['nullable', 'boolean'],
        ];
    }
}

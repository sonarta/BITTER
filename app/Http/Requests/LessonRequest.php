<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'transcript' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'duration_seconds' => ['required', 'integer', 'min:0'],
            'is_preview' => ['boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'resources' => ['nullable', 'array'],
            'resources.*.id' => ['sometimes', 'string'],
            'resources.*.title' => ['required_with:resources', 'string', 'max:255'],
            'resources.*.url' => ['required_with:resources', 'url', 'max:2048'],
            'resources.*.type' => ['required_with:resources', 'string', 'max:50'],
        ];
    }
}

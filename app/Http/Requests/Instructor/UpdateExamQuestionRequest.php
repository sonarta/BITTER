<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamQuestionRequest extends FormRequest
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
            'type' => ['required', 'string', 'in:single,multiple,true_false,essay'],
            'prompt' => ['required', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'options' => ['array'],
            'options.*.id' => ['nullable', 'string'],
            'options.*.text' => ['required_with:options', 'string', 'max:255'],
            'options.*.is_correct' => ['required_with:options', 'boolean'],
            'options.*.sort_order' => ['required_with:options', 'integer', 'min:0'],
        ];
    }
}

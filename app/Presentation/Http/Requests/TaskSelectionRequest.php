<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskSelectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selected_option' => ['nullable', 'string', 'max:100'],
            'selection_payload' => ['nullable', 'array'],
            'selection_payload.font_family' => ['sometimes', 'string', 'max:100'],
            'selection_payload.font_size' => ['sometimes', 'string', 'max:100'],
            'selection_payload.design_styles' => ['sometimes', 'array', 'min:1', 'max:5'],
            'selection_payload.design_styles.*' => ['string', 'distinct', 'max:100'],
            'active_duration_ms' => ['required', 'integer', 'min:0', 'max:86400000'],
            'idle_duration_ms' => ['sometimes', 'integer', 'min:0', 'max:86400000'],
            'change_count' => ['sometimes', 'integer', 'min:0', 'max:10000'],
        ];
    }
}

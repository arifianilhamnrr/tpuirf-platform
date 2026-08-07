<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrustEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trust_score' => ['required', 'numeric', 'between:1,5'],
            'transparency_score' => ['required', 'numeric', 'between:1,5'],
            'ui_satisfaction' => ['required', 'numeric', 'between:1,5'],
            'recommendation_quality' => ['nullable', 'numeric', 'between:1,5'],
            'feedback' => ['nullable', 'string', 'max:5000'],
            'additional_data' => ['nullable', 'array'],
        ];
    }
}

<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreXaiEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return collect(array_keys(config('xai_evaluation.items')))
            ->mapWithKeys(fn (string $key) => [$key => ['required', 'integer', 'between:1,5']])
            ->all();
    }

    public function attributes(): array
    {
        return collect(config('xai_evaluation.items'))
            ->mapWithKeys(fn (array $item, string $key) => [$key => $item['statement']])
            ->all();
    }

    public function messages(): array
    {
        return [
            'required' => 'Pilih satu jawaban untuk pernyataan ini.',
            'integer' => 'Jawaban harus menggunakan skala Likert 1–5.',
            'between' => 'Jawaban harus berada pada skala 1 sampai 5.',
        ];
    }
}

<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartPreferenceExperimentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'consent_accepted' => ['required', 'accepted'],
            'consent_version' => [
                'required',
                'string',
                Rule::in([(string) config('experiment.consent_version')]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'consent_accepted.required' => 'Persetujuan partisipasi wajib dipilih.',
            'consent_accepted.accepted' => 'Anda harus menyetujui persetujuan partisipasi untuk melanjutkan.',
            'consent_version.in' => 'Versi persetujuan tidak sesuai dengan protokol aktif.',
        ];
    }
}

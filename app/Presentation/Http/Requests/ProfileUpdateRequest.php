<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gender' => ['required', 'string', 'in:male,female'],
            'education' => ['required', 'string', 'in:high-school,bachelor,master,doctorate,other'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'digital_literacy' => ['required', 'integer', 'min:1', 'max:5'],
            'device_type' => ['required', 'string', 'in:desktop,laptop,tablet,smartphone'],
            'visual_preference' => ['required', 'string', 'in:minimal,vibrant,classic,modern'],
            'context_environment' => ['required', 'string', 'in:home,office,school,public,other'],
            'ai_familiarity' => ['required', 'integer', 'min:1', 'max:5'],
            'daily_device_usage' => ['required', 'string', 'in:under_1,1_3,3_5,5_8,over_8'],
            'device_usage_time' => ['required', 'string', 'in:morning,afternoon,evening,night'],
            'primary_device_purpose' => ['required', 'string', 'in:communication,education,shopping,entertainment,finance,productivity,social_media,other'],
        ];
    }

    public function messages(): array
    {
        return [
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin yang dipilih tidak valid.',
            'education.required' => 'Pendidikan wajib dipilih.',
            'occupation.max' => 'Pekerjaan maksimal 255 karakter.',
            'digital_literacy.required' => 'Tingkat literasi digital wajib dipilih.',
            'digital_literacy.min' => 'Tingkat literasi digital minimal 1.',
            'digital_literacy.max' => 'Tingkat literasi digital maksimal 5.',
            'device_type.required' => 'Jenis perangkat wajib dipilih.',
            'visual_preference.required' => 'Preferensi visual wajib dipilih.',
            'context_environment.required' => 'Lingkungan penggunaan wajib dipilih.',
            'ai_familiarity.required' => 'Tingkat pemahaman AI wajib dipilih.',
            'ai_familiarity.min' => 'Tingkat pemahaman AI minimal 1.',
            'ai_familiarity.max' => 'Tingkat pemahaman AI maksimal 5.',
        ];
    }
}

<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Silakan masukkan nama lengkap Anda.',
            'date_of_birth.required' => 'Silakan masukkan tanggal lahir Anda.',
            'date_of_birth.date' => 'Silakan masukkan tanggal yang valid.',
        ];
    }
}

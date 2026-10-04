<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'     => ['required', 'string', 'email', 'max:255'],
            'password'  => ['required', 'string', 'min:8', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'     => 'The email field is required.',
            'email.email'        => 'The email address must be valid.',
            'email.max'          => 'The email must not exceed 255 characters.',

            'password.required'  => 'The password field is required.',
            'password.min'       => 'The password must be at least 8 characters.',
            'password.max'       => 'The password must not exceed 100 characters.',
        ];
    }
}
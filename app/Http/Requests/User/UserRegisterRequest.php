<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
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
            'name'      => ['required', 'string', 'min:3', 'max:150'],
            'full_name' => ['required', 'string', 'min:3', 'max:150'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'The username field is required.',
            'name.min'           => 'The username must be at least 3 characters.',
            'name.max'           => 'The username must not exceed 150 characters.',

            'full_name.required' => 'The full name field is required.',
            'full_name.min'      => 'The full name must be at least 3 characters.',
            'full_name.max'      => 'The full name must not exceed 150 characters.',

            'email.required'     => 'The email field is required.',
            'email.email'        => 'The email address must be valid.',
            'email.max'          => 'The email must not exceed 255 characters.',
            'email.unique'       => 'This email address is already registered.',

            'password.required'  => 'The password field is required.',
            'password.min'       => 'The password must be at least 8 characters.',
            'password.max'       => 'The password must not exceed 100 characters.',
        ];
    }
}
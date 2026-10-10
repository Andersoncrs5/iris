<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            'full_name.max'      => 'The full name must not exceed 150 characters.'
        ];
    }
}
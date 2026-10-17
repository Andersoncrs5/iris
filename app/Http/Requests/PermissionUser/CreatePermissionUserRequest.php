<?php

namespace App\Http\Requests\PermissionUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePermissionUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->whereNull('deleted_at'),
            ],

            'permission_id' => [
                'required',
                'integer',
                Rule::exists('permissions', 'id')
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Select a user.',
            'user_id.integer' => 'The selected user ID is invalid.',
            'user_id.exists' => 'The selected user does not exist or has been deleted.',

            'permission_id.required' => 'Select a permission.',
            'permission_id.integer' => 'The selected permission ID is invalid.',
            'permission_id.exists' => 'The selected permission does not exist or has been deleted.',
        ];
    }
}
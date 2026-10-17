<?php

namespace App\Http\Requests\PermissionRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePermissionRequestRequest extends FormRequest
{
    /**
     * Determina se o usuário pode realizar a requisição.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('permission.request.create') ?? false;
    }

    /**
     * Retorna as regras de validação.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'permission_id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('permissions', 'id'),
            ],

            'reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * Mensagens personalizadas de validação.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'permission_id.required' => 'Please select a permission to request.',
            'permission_id.integer' => 'The selected permission ID must be an integer.',
            'permission_id.min' => 'The selected permission ID must be positive.',
            'permission_id.exists' => 'The selected permission does not exist.',

            'reason.required' => 'The justification is required.',
            'reason.string' => 'The justification must be valid text.',
            'reason.max' => 'The justification cannot exceed 2,000 characters.',
        ];
    }

    /**
     * Nomes amigáveis dos campos.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'permission_id' => 'permission',
            'reason' => 'justification',
        ];
    }
}
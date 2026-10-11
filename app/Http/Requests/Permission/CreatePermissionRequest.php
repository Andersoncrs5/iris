<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('permission.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug') ? Str::slug($this->slug) : Str::slug($this->name),
            'module' => $this->filled('module') ? strtolower(trim($this->module)) : 'system',
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('permissions', 'name')->whereNull('deleted_at'),
            ],
            'slug' => [
                'required',
                'string',
                'max:200',
                Rule::unique('permissions', 'slug')->whereNull('deleted_at'),
            ],
            'module' => [
                'nullable',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The permission name is required.',
            'name.max' => 'The permission name may not be greater than 150 characters.',
            'name.unique' => 'A permission with this name already exists.',
            'slug.required' => 'The slug is required.',
            'slug.max' => 'The slug may not be greater than 200 characters.',
            'slug.unique' => 'This permission slug is already in use.',
            'module.max' => 'The module name may not exceed 100 characters.',
            'description.max' => 'The description may not be greater than 500 characters.',
        ];
    }
}
<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('permission.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $slug = $this->input('slug');
        $module = $this->input('module');
        $description = $this->input('description');

        $this->merge([
            'name' => is_string($name)
                ? trim($name)
                : $name,

            'slug' => is_string($slug)
                ? strtolower(trim($slug))
                : $slug,

            'module' => is_string($module) && trim($module) !== ''
                ? strtolower(trim($module))
                : 'system',

            'description' => is_string($description)
                && trim($description) !== ''
                    ? trim($description)
                    : null,
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('permissions', 'name')
                    ->ignore($id)
                    ->whereNull('deleted_at'),
            ],

            'slug' => [
                'required',
                'string',
                'max:200',
                'regex:/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/',
                Rule::unique('permissions', 'slug')
                    ->ignore($id)
                    ->whereNull('deleted_at'),
            ],

            'module' => [
                'required',
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
            'name.max' => 'The permission name may not exceed 150 characters.',
            'name.unique' => 'A permission with this name already exists.',

            'slug.required' => 'The permission slug is required.',
            'slug.max' => 'The permission slug may not exceed 200 characters.',
            'slug.regex' => 'The slug may contain lowercase letters, numbers, dots, underscores and hyphens.',
            'slug.unique' => 'This permission slug is already in use.',

            'module.required' => 'The module is required.',
            'module.max' => 'The module name may not exceed 100 characters.',

            'description.max' => 'The description may not exceed 500 characters.',
        ];
    }
}
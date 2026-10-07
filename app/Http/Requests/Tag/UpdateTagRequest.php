<?php

namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tag.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug') ? Str::slug($this->slug) : Str::slug($this->name),
            'is_active' => $this->boolean('is_active', true),
            'type' => $this->filled('type') ? strtoupper($this->type) : 'GENERAL',
        ]);
    }

    public function rules(): array
    {
        $tagId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tags', 'name')->ignore($tagId)->whereNull('deleted_at'),
            ],
            'slug' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tags', 'slug')->ignore($tagId)->whereNull('deleted_at'),
            ],
            'color' => ['nullable', 'string', 'max:20'],
            'type' => [
                'nullable',
                'string',
                Rule::in(['GENERAL', 'SYSTEM', 'CONTENT', 'PRODUCT', 'STATUS']),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The tag name is required.',
            'name.max' => 'The tag name may not be greater than 100 characters.',
            'name.unique' => 'A tag with this name already exists.',
            'slug.required' => 'The slug is required.',
            'slug.max' => 'The slug may not be greater than 100 characters.',
            'slug.unique' => 'This slug is already in use.',
            'color.max' => 'The color code may not exceed 20 characters.',
            'type.in' => 'The selected tag type is invalid.',
            'description.max' => 'The description may not be greater than 255 characters.',
        ];
    }
}
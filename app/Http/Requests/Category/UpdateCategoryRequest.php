<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('category.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug') ? Str::slug($this->slug) : Str::slug($this->name),
            'is_active' => $this->boolean('is_active', false),
            'display_order' => $this->filled('display_order') ? (int) $this->display_order : 0,
        ]);
    }

    public function rules(): array
    {
        $categoryId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')
                    ->ignore($categoryId)
                    ->whereNull('deleted_at'),
            ],
            'slug' => [
                'required',
                'string',
                'max:150',
                Rule::unique('categories', 'slug')
                    ->ignore($categoryId)
                    ->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
                Rule::notIn([$categoryId]),
            ],
            'icon' => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da categoria é obrigatório.',
            'name.max' => 'O nome da categoria não pode ter mais de 100 caracteres.',
            'name.unique' => 'Já existe uma categoria cadastrada com este nome.',
            'slug.unique' => 'Esta slug já está em uso.',
            'description.max' => 'A descrição não pode exceder 500 caracteres.',
            'parent_id.exists' => 'A categoria pai selecionada não existe.',
            'parent_id.not_in' => 'Uma categoria não pode ser pai dela mesma.',
            'display_order.integer' => 'A ordem de exibição deve ser um número inteiro.',
            'display_order.min' => 'A ordem de exibição não pode ser negativa.',
        ];
    }
}
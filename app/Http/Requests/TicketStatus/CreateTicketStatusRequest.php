<?php

namespace App\Http\Requests\TicketStatus;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreateTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ticket.status.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name ?? ''),
            'code' => Str::upper(trim($this->code ?? '')),
            'description' => $this->filled('description')
                ? trim($this->description)
                : null,
            'color' => $this->filled('color')
                ? trim($this->color)
                : null,
            'icon' => $this->filled('icon')
                ? trim($this->icon)
                : null,
            'display_order' => $this->input('display_order', 0),
            'is_initial' => $this->boolean('is_initial'),
            'is_final' => $this->boolean('is_final'),
            'stops_sla' => $this->boolean('stops_sla'),
            'requires_comment' => $this->boolean('requires_comment'),
            'is_active' => $this->has('is_active')
                ? $this->boolean('is_active')
                : true,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'unique:ticket_statuses,name',
            ],

            'code' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'alpha_dash',
                'unique:ticket_statuses,code',
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'color' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^(#[0-9A-Fa-f]{3,8}|[a-zA-Z]+)$/',
            ],

            'icon' => [
                'nullable',
                'string',
                'max:100',
            ],

            'display_order' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
            ],

            'is_initial' => [
                'boolean',
            ],

            'is_final' => [
                'boolean',
            ],

            'stops_sla' => [
                'boolean',
            ],

            'requires_comment' => [
                'boolean',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The status name is required.',
            'name.min' => 'The status name must be at least 2 characters.',
            'name.max' => 'The status name must not exceed 100 characters.',
            'name.unique' => 'A ticket status with this name already exists.',

            'code.required' => 'The status code is required.',
            'code.min' => 'The status code must be at least 2 characters.',
            'code.max' => 'The status code must not exceed 50 characters.',
            'code.alpha_dash' => 'The status code may only contain letters, numbers, dashes and underscores.',
            'code.unique' => 'A ticket status with this code already exists.',

            'description.max' => 'The description must not exceed 500 characters.',

            'color.max' => 'The color must not exceed 30 characters.',
            'color.regex' => 'The color must be a valid HEX color or CSS color name.',

            'icon.max' => 'The icon class must not exceed 100 characters.',

            'display_order.required' => 'The display order is required.',
            'display_order.integer' => 'The display order must be a number.',
            'display_order.min' => 'The display order cannot be negative.',

            'is_initial.boolean' => 'The initial status value must be true or false.',
            'is_final.boolean' => 'The final status value must be true or false.',
            'stops_sla.boolean' => 'The SLA setting must be true or false.',
            'requires_comment.boolean' => 'The comment requirement must be true or false.',
            'is_active.boolean' => 'The active status must be true or false.',
        ];
    }
}
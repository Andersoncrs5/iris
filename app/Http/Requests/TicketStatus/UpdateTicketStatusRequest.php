<?php

namespace App\Http\Requests\TicketStatus;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ticket.status.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => $this->filled('code') ? Str::slug($this->code, '_') : Str::slug($this->name, '_'),
            'is_initial' => $this->boolean('is_initial', false),
            'is_final' => $this->boolean('is_final', false),
            'stops_sla' => $this->boolean('stops_sla', false),
            'requires_comment' => $this->boolean('requires_comment', false),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    public function rules(): array
    {
        $statusId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('ticket_statuses', 'name')->ignore($statusId)->whereNull('deleted_at'),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('ticket_statuses', 'code')->ignore($statusId)->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'string', 'max:30'],
            'icon' => ['nullable', 'string', 'max:100'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_initial' => ['nullable', 'boolean'],
            'is_final' => ['nullable', 'boolean'],
            'stops_sla' => ['nullable', 'boolean'],
            'requires_comment' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The ticket status name is required.',
            'name.max' => 'The ticket status name may not be greater than 100 characters.',
            'name.unique' => 'A ticket status with this name already exists.',
            'code.required' => 'The status code is required.',
            'code.max' => 'The status code may not be greater than 50 characters.',
            'code.unique' => 'This status code is already in use.',
            'description.max' => 'The description may not be greater than 500 characters.',
        ];
    }
}
<?php

namespace App\Http\Requests\TicketPriority;

use App\Models\TicketPriorityModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketPriorityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ticket.priority.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['name', 'code', 'description', 'color', 'icon'] as $field) {
            if (!$this->exists($field)) {
                continue;
            }

            $value = $this->input($field);

            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($field === 'code') {
                $value = strtoupper($value);
            }

            $normalized[$field] = $value === '' ? null : $value;
        }

        foreach ([
            'version',
            'level',
            'response_time_minutes',
            'resolution_time_minutes',
            'escalation_time_minutes',
        ] as $field) {
            if ($this->input($field) === '') {
                $normalized[$field] = null;
            }
        }

        foreach (['is_default', 'is_active'] as $field) {
            if (!$this->exists($field)) {
                continue;
            }

            $value = $this->input($field);

            $boolean = filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            if ($boolean !== null) {
                $normalized[$field] = $boolean;
            }
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $id = $this->priorityId();

        return [
            'version' => [
                'required',
                'integer',
                'min:1',
            ],

            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('ticket_priorities', 'name')->ignore($id, 'id'),
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9._-]+$/',
                Rule::unique('ticket_priorities', 'code')->ignore($id, 'id'),
            ],

            'level' => [
                'required',
                'integer',
                'min:1',
                'max:32767',
                Rule::unique('ticket_priorities', 'level')->ignore($id, 'id'),
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
            ],

            'icon' => [
                'nullable',
                'string',
                'max:100',
            ],

            'response_time_minutes' => [
                'nullable',
                'integer',
                'min:1',
                'max:2147483647',
            ],

            'resolution_time_minutes' => [
                'nullable',
                'integer',
                'min:1',
                'max:2147483647',
            ],

            'escalation_time_minutes' => [
                'nullable',
                'integer',
                'min:1',
                'max:2147483647',
            ],

            'is_default' => [
                'sometimes',
                'boolean',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'version.required' => 'The record version is required.',
            'version.integer' => 'The record version must be an integer.',
            'version.min' => 'The record version must be at least 1.',

            'name.required' => 'The priority name is required.',
            'name.string' => 'The priority name must be a string.',
            'name.max' => 'The priority name may not exceed 100 characters.',
            'name.unique' => 'A priority with this name already exists.',

            'code.required' => 'The priority code is required.',
            'code.string' => 'The priority code must be a string.',
            'code.max' => 'The priority code may not exceed 50 characters.',
            'code.regex' => 'The priority code may contain only letters, numbers, dots, underscores, and hyphens.',
            'code.unique' => 'A priority with this code already exists.',

            'level.required' => 'The priority level is required.',
            'level.integer' => 'The priority level must be an integer.',
            'level.min' => 'The priority level must be at least 1.',
            'level.max' => 'The priority level may not exceed 32767.',
            'level.unique' => 'A priority with this level already exists.',

            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not exceed 500 characters.',

            'color.string' => 'The color must be a string.',
            'color.max' => 'The color may not exceed 30 characters.',

            'icon.string' => 'The icon must be a string.',
            'icon.max' => 'The icon may not exceed 100 characters.',

            'response_time_minutes.integer' => 'The response time must be an integer.',
            'response_time_minutes.min' => 'The response time must be at least 1 minute.',
            'response_time_minutes.max' => 'The response time is too large.',

            'resolution_time_minutes.integer' => 'The resolution time must be an integer.',
            'resolution_time_minutes.min' => 'The resolution time must be at least 1 minute.',
            'resolution_time_minutes.max' => 'The resolution time is too large.',

            'escalation_time_minutes.integer' => 'The escalation time must be an integer.',
            'escalation_time_minutes.min' => 'The escalation time must be at least 1 minute.',
            'escalation_time_minutes.max' => 'The escalation time is too large.',

            'is_default.boolean' => 'The default flag must be true or false.',
            'is_active.boolean' => 'The active flag must be true or false.',
        ];
    }

    /**
     * Resolve the ticket priority identifier from the route.
     */
    private function priorityId(): int|string|null
    {
        $priority = $this->route('ticketPriority')
            ?? $this->route('ticket_priority')
            ?? $this->route('priority')
            ?? $this->route('id');

        if ($priority instanceof TicketPriorityModel) {
            return $priority->getKey();
        }

        if (is_int($priority) || is_string($priority)) {
            return $priority;
        }

        return null;
    }
}
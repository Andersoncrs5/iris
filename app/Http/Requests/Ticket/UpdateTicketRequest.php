<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ticket.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['subject', 'description'] as $field) {
            if ($this->exists($field) && is_string($this->input($field))) {
                $normalized[$field] = trim($this->input($field));
            }
        }

        foreach ([
            'version',
            'agent_id',
            'team_id',
            'category_id',
            'priority_id',
        ] as $field) {
            if ($this->exists($field) && $this->input($field) === '') {
                $normalized[$field] = null;
            }
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        return [
            'version' => [
                'required',
                'integer',
                'min:1',
            ],

            'subject' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'category_id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('categories', 'id')
                    ->whereNull('deleted_at'),
            ],

            'priority_id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('ticket_priorities', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true),
            ],

            'agent_id' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
                Rule::exists('users', 'id'),
            ],

            // The migration does not define a team foreign key.
            'team_id' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'version.required' => 'The record version is required.',
            'version.integer' => 'The record version must be an integer.',
            'version.min' => 'The record version must be at least 1.',

            'subject.required' => 'The ticket subject is required.',
            'subject.string' => 'The ticket subject must be a string.',
            'subject.max' => 'The ticket subject may not exceed 255 characters.',

            'description.required' => 'The ticket description is required.',
            'description.string' => 'The ticket description must be a string.',

            'category_id.required' => 'Please select a ticket category.',
            'category_id.integer' => 'The category ID must be an integer.',
            'category_id.min' => 'The category ID must be valid.',
            'category_id.exists' => 'The selected category does not exist or is no longer available.',

            'priority_id.required' => 'Please select a ticket priority.',
            'priority_id.integer' => 'The priority ID must be an integer.',
            'priority_id.min' => 'The priority ID must be valid.',
            'priority_id.exists' => 'The selected priority does not exist or is inactive.',

            'agent_id.integer' => 'The assigned agent ID must be an integer.',
            'agent_id.min' => 'The assigned agent ID must be valid.',
            'agent_id.exists' => 'The selected agent does not exist.',

            'team_id.integer' => 'The assigned team ID must be an integer.',
            'team_id.min' => 'The assigned team ID must be valid.',
        ];
    }
}

<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTicketRequest extends FormRequest
{
    private const SOURCES = [
        'WEB',
        'EMAIL',
        'API',
        'CHAT',
        'WHATSAPP',
        'PHONE',
    ];

    public function authorize(): bool
    {
        return $this->user()?->can('ticket.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [
            // Never trust a user ID supplied by the client.
            'user_id' => $this->user()?->getAuthIdentifier(),
        ];

        foreach (['subject', 'description'] as $field) {
            if ($this->exists($field) && is_string($this->input($field))) {
                $normalized[$field] = trim($this->input($field));
            }
        }

        foreach ([
            'agent_id',
            'team_id',
            'category_id',
            'priority_id',
        ] as $field) {
            if ($this->exists($field) && $this->input($field) === '') {
                $normalized[$field] = null;
            }
        }

        $source = strtoupper(
            trim((string) $this->input('source', 'WEB'))
        );

        $normalized['source'] = $source === '' ? 'WEB' : $source;

        $this->merge($normalized);
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('users', 'id'),
            ],

            'agent_id' => [
                'nullable',
                'integer',
                'min:1',
                Rule::exists('users', 'id'),
            ],

            // The migration does not define a team foreign key.
            // Validate the identifier format until the teams schema is defined.
            'team_id' => [
                'nullable',
                'integer',
                'min:1',
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

            'subject' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'source' => [
                'required',
                'string',
                Rule::in(self::SOURCES),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'The ticket owner could not be identified.',
            'user_id.integer' => 'The ticket owner ID must be an integer.',
            'user_id.min' => 'The ticket owner ID must be valid.',
            'user_id.exists' => 'The ticket owner does not exist.',

            'agent_id.integer' => 'The assigned agent ID must be an integer.',
            'agent_id.min' => 'The assigned agent ID must be valid.',
            'agent_id.exists' => 'The selected agent does not exist.',

            'team_id.integer' => 'The assigned team ID must be an integer.',
            'team_id.min' => 'The assigned team ID must be valid.',

            'category_id.required' => 'Please select a ticket category.',
            'category_id.integer' => 'The category ID must be an integer.',
            'category_id.min' => 'The category ID must be valid.',
            'category_id.exists' => 'The selected category does not exist or is no longer available.',

            'priority_id.required' => 'Please select a ticket priority.',
            'priority_id.integer' => 'The priority ID must be an integer.',
            'priority_id.min' => 'The priority ID must be valid.',
            'priority_id.exists' => 'The selected priority does not exist or is inactive.',

            'subject.required' => 'The ticket subject is required.',
            'subject.string' => 'The ticket subject must be a string.',
            'subject.max' => 'The ticket subject may not exceed 255 characters.',

            'description.required' => 'The ticket description is required.',
            'description.string' => 'The ticket description must be a string.',

            'source.required' => 'The ticket source is required.',
            'source.string' => 'The ticket source must be a string.',
            'source.in' => 'The selected ticket source is invalid.',
        ];
    }
}

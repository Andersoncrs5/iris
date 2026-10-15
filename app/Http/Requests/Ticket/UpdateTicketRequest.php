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

        foreach ([
            'version',
            'status_id',
            'first_response_at',
            'resolved_at',
            'closed_at',
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

            'status_id' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                Rule::exists('ticket_statuses', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true),
            ],

            'first_response_at' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'resolved_at' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'closed_at' => [
                'sometimes',
                'nullable',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'version.required' => 'The record version is required.',
            'version.integer' => 'The record version must be an integer.',
            'version.min' => 'The record version must be at least 1.',

            'status_id.required' => 'The ticket status is required when provided.',
            'status_id.integer' => 'The ticket status ID must be an integer.',
            'status_id.min' => 'The ticket status ID must be valid.',
            'status_id.exists' => 'The selected status does not exist or is inactive.',

            'first_response_at.date' => 'The first response timestamp must be a valid date.',
            'resolved_at.date' => 'The resolution timestamp must be a valid date.',
            'closed_at.date' => 'The closure timestamp must be a valid date.',
        ];
    }
}
<?php

namespace App\Http\Requests\Ticket;

use App\Models\TicketModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMyOwnTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user || !$user->can('ticket.own.update')) {
            return false;
        }

        return TicketModel::query()
            ->whereKey($this->route('id'))
            ->where('user_id', $user->getAuthIdentifier())
            ->exists();
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['subject', 'description', 'satisfaction_comment'] as $field) {
            if ($this->exists($field) && is_string($this->input($field))) {
                $value = trim($this->input($field));

                $normalized[$field] = $value === '' && $field === 'satisfaction_comment'
                    ? null
                    : $value;
            }
        }

        foreach (['version', 'satisfaction_rating'] as $field) {
            if ($this->exists($field) && $this->input($field) === '') {
                $normalized[$field] = null;
            }
        }

        if ($this->exists('source') && is_string($this->input('source'))) {
            $normalized['source'] = strtoupper(trim($this->input('source')));
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        return [
            // Optimistic concurrency control.
            'version' => [
                'required',
                'integer',
                'min:1',
            ],

            // Owner-editable fields.
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
                Rule::in([
                    'WEB',
                    'EMAIL',
                    'API',
                    'CHAT',
                    'WHATSAPP',
                    'PHONE',
                ]),
            ],

            'satisfaction_rating' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
                'max:65535',
            ],

            'satisfaction_comment' => [
                'sometimes',
                'nullable',
                'string',
                'max:500',
            ],

            'is_escalated' => [
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

            'subject.required' => 'The ticket subject is required.',
            'subject.string' => 'The ticket subject must be a string.',
            'subject.max' => 'The ticket subject may not exceed 255 characters.',

            'description.required' => 'The ticket description is required.',
            'description.string' => 'The ticket description must be a string.',

            'source.required' => 'The ticket source is required.',
            'source.string' => 'The ticket source must be a string.',
            'source.in' => 'The selected ticket source is invalid.',

            'satisfaction_rating.integer' => 'The satisfaction rating must be an integer.',
            'satisfaction_rating.min' => 'The satisfaction rating cannot be negative.',
            'satisfaction_rating.max' => 'The satisfaction rating is out of range.',

            'satisfaction_comment.string' => 'The satisfaction comment must be a string.',
            'satisfaction_comment.max' => 'The satisfaction comment may not exceed 500 characters.',

            'is_escalated.boolean' => 'The escalation flag must be true or false.',
        ];
    }
}
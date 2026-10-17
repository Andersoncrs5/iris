<?php

namespace App\Http\Requests\TicketMessage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ticket.message.update') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'message' => [
                'required',
                'string',
                'max:10000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.required' => 'The message field is required.',
            'message.string' => 'The message must be a valid text.',
            'message.max' => 'The message may not exceed 10,000 characters.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'message' => 'message content',
        ];
    }
}
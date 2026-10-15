<?php

namespace App\Http\Requests\TicketMessage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTicketMessageRequest extends FormRequest
{
    /**
     * Determina se o usuário pode realizar a requisição.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('ticket.message.create') ?? false;
    }

    /**
     * Retorna as regras de validação.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $ticketId = (int) $this->route('id');

        return [
            'message' => [
                'required',
                'string',
                'max:10000',
            ],

            'parent_id' => [
                'nullable',
                'integer',
                'min:1',
                Rule::exists('ticket_messages', 'id')
                    ->where(
                        fn ($query) => $query
                            ->where('ticket_id', $ticketId)
                            ->whereNull('deleted_at'),
                    ),
            ],
        ];
    }

    /**
     * Mensagens personalizadas de validação.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.required' => 'The comment message is required.',
            'message.string' => 'The comment must be a valid text.',
            'message.max' => 'The comment cannot exceed 10,000 characters.',
            'parent_id.integer' => 'The parent message ID must be an integer.',
            'parent_id.min' => 'The parent message ID must be positive.',
            'parent_id.exists' => 'The message you are replying to does not belong to this ticket or no longer exists.',
        ];
    }

    /**
     * Nomes amigáveis dos campos.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'message' => 'comment',
            'parent_id' => 'parent message',
        ];
    }
}
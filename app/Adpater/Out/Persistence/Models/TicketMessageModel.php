<?php

namespace App\Adapter\Out\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketMessageModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Tabela associada ao Model.
     *
     * @var string
     */
    protected $table = 'ticket_messages';

    /**
     * O ID (Snowflake) não é auto-incrementável.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Tipo da chave primária.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Atributos atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'ticket_id',
        'user_id',
        'message',
        'type',
        'is_internal',
        'is_edited',
        'edited_at',
        'attachments',
        'version',
    ];

    /**
     * Casts de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'ticket_id' => 'integer',
            'user_id' => 'integer',
            'is_internal' => 'boolean',
            'is_edited' => 'boolean',
            'edited_at' => 'datetime',
            'attachments' => 'array',
            'version' => 'integer',
        ];
    }

    /**
     * Chamado ao qual a mensagem pertence.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * Autor da mensagem (usuário ou agente).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    /**
     * Anexos associados diretamente a esta mensagem.
     */
    public function messageAttachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class, 'message_id');
    }
}
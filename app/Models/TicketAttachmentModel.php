<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\Attributes\Ticket;

class TicketAttachmentModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Tabela associada ao Model.
     *
     * @var string
     */
    protected $table = 'ticket_attachments';

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
        'message_id',
        'user_id',
        'original_filename',
        'stored_filename',
        'storage_path',
        'disk',
        'mime_type',
        'size_bytes',
        'checksum',
        'is_internal',
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
            'message_id' => 'integer',
            'user_id' => 'integer',
            'size_bytes' => 'integer',
            'is_internal' => 'boolean',
            'version' => 'integer',
        ];
    }

    /**
     * Chamado ao qual o anexo pertence.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * Mensagem associada ao anexo (caso exista).
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(TicketMessageModel::class, 'message_id');
    }

    /**
     * Usuário que enviou o anexo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }
}
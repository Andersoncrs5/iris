<?php

namespace App\Adapter\Out\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketTag extends Model
{
    use HasFactory;

    /**
     * Tabela associada ao Model.
     *
     * @var string
     */
    protected $table = 'ticket_tags';

    /**
     * Apenas created_at é gerenciado pelo Eloquent nesta pivot.
     */
    public $timestamps = false;

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
        'tag_id',
        'created_by',
        'version',
        'created_at',
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
            'tag_id' => 'integer',
            'created_by' => 'integer',
            'version' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Chamado associado.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * Tag associada.
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'tag_id');
    }

    /**
     * Usuário/Agente que vinculou a tag ao chamado.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
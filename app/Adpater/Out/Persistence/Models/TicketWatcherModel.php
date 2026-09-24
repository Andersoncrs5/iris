<?php

namespace App\Adapter\Out\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketWatcherModel extends Model
{
    use HasFactory;

    /**
     * Tabela associada ao Model.
     *
     * @var string
     */
    protected $table = 'ticket_watchers';

    /**
     * Apenas created_at é gerenciado pelo Eloquent nesta pivot/relacionamento.
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
        'user_id',
        'added_by',
        'reason',
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
            'user_id' => 'integer',
            'added_by' => 'integer',
            'version' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Chamado sendo observado.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * Usuário que está observando o chamado.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    /**
     * Usuário/Agente que vinculou o observador ao chamado.
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'added_by');
    }
}
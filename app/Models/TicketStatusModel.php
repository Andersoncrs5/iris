<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketStatusModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Tabela associada ao Model.
     *
     * @var string
     */
    protected $table = 'ticket_statuses';

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
        'name',
        'code',
        'description',
        'color',
        'icon',
        'display_order',
        'is_initial',
        'is_final',
        'stops_sla',
        'requires_comment',
        'is_active',
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
            'display_order' => 'integer',
            'is_initial' => 'boolean',
            'is_final' => 'boolean',
            'stops_sla' => 'boolean',
            'requires_comment' => 'boolean',
            'is_active' => 'boolean',
            'version' => 'integer',
        ];
    }
}
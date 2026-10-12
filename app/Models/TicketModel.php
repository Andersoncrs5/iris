<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Tabela associada ao Model.
     *
     * @var string
     */
    protected $table = 'tickets';

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
        'ticket_number',
        'user_id',
        'agent_id',
        'team_id',
        'category_id',
        'status_id',
        'priority_id',
        'subject',
        'description',
        'source',
        'satisfaction_rating',
        'satisfaction_comment',
        'is_escalated',
        'version',
        'opened_at',
        'last_activity_at',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'due_at',
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
            'ticket_number' => 'integer',
            'user_id' => 'integer',
            'agent_id' => 'integer',
            'team_id' => 'integer',
            'category_id' => 'integer',
            'status_id' => 'integer',
            'priority_id' => 'integer',
            'satisfaction_rating' => 'integer',
            'is_escalated' => 'boolean',
            'version' => 'integer',
            'opened_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'due_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatusModel::class, 'status_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TicketPriorityModel::class, 'priority_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessageModel::class, 'ticket_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachmentModel::class, 'ticket_id');
    }
}
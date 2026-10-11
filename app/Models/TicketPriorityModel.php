<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Eloquent model representing a ticket priority.
 *
 * Manages ticket priority data, including its severity level, visual
 * identification, SLA time limits, default status, and activation state.
 *
 * Supports soft deletion and uses a Snowflake-generated identifier
 * instead of an auto-incrementing primary key.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $level
 * @property string|null $description
 * @property string|null $color
 * @property string|null $icon
 * @property int|null $response_time_minutes
 * @property int|null $resolution_time_minutes
 * @property int|null $escalation_time_minutes
 * @property bool $is_default
 * @property bool $is_active
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static> query()
 * @method static \Illuminate\Database\Eloquent\Builder<static> withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static> onlyTrashed()
 * @method static static create(array<string, mixed> $attributes = [])
 * @method static static findOrFail(mixed $id, array|string $columns = ['*'])
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class TicketPriorityModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ticket_priorities';

    /**
     * Indicates whether the primary key is auto-incrementing.
     *
     * Snowflake identifiers are generated externally.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'code',
        'level',
        'description',
        'color',
        'icon',
        'response_time_minutes',
        'resolution_time_minutes',
        'escalation_time_minutes',
        'is_default',
        'is_active',
        'version',
    ];

    /**
     * Get the attribute casting definitions.
     *
     * Converts database values into their expected PHP types
     * when accessing model attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'level' => 'integer',
            'response_time_minutes' => 'integer',
            'resolution_time_minutes' => 'integer',
            'escalation_time_minutes' => 'integer',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'version' => 'integer',
        ];
    }
}
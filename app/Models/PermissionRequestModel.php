<?php

namespace App\Models;

use App\Enums\PermissionRequestStatus\PermissionRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission;

/**
 * Model responsável pelas solicitações de permissões do sistema.
 *
 * Representa uma solicitação de acesso realizada por um usuário,
 * permitindo registrar a permissão desejada e acompanhar sua análise
 * administrativa, incluindo aprovação, rejeição ou cancelamento.
 *
 * @property int $id
 * @property int $user_id
 * @property int $permission_id
 * @property string $reason
 * @property PermissionRequestStatus $status
 * @property int|null $reviewed_by
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property string|null $review_comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $user
 * @property-read Permission $permission
 * @property-read User|null $reviewer
 */
class PermissionRequestModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'permission_requests';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'permission_id',
        'reason',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'permission_id' => 'integer',
        'reviewed_by' => 'integer',
        'status' => PermissionRequestStatus::class,
        'reviewed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Permission, $this>
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(PermissionModel::class, 'permission_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $permission_id
 * @property int $user_id
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read User $user
 * @property-read PermissionModel $permission
 */
class PermissionUserModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permission_user';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'permission_id',
        'user_id',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'permission_id' => 'integer',
            'user_id' => 'integer',
            'version' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(PermissionModel::class, 'permission_id');
    }
}
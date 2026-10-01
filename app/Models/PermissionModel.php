<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $module
 * @property string|null $description
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserModel> $users
 */
class PermissionModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permissions';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'module',
        'description',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'version' => 'integer',
        ];
    }

    /**
     * Relacionamento N:N com Usuários.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            UserModel::class,
            'permission_user',
            'permission_id',
            'user_id'
        )
        ->withPivot(['id', 'version'])
        ->withTimestamps();
    }
}
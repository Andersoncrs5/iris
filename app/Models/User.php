<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
        'full_name',
        'email',
        'password',
        'status',
        'permissions',
        'email_verified_at',
        'last_login_at',
        'version',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'id'                => 'integer',
            'name'              => 'string',
            'full_name'         => 'string',
            'email'             => 'string',
            'password'          => 'hashed',
            'status'            => 'string',
            'permissions'       => 'array',
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'version'           => 'integer',
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
            'deleted_at'        => 'datetime',
        ];
    }

    /**
     * Permissões vinculadas ao usuário através da tabela pivot.
     */
    public function permissionRelations(): BelongsToMany
    {
        return $this->belongsToMany(
            PermissionModel::class,
            'permission_user',
            'user_id',
            'permission_id'
        )
        ->withPivot([
            'id',
            'version',
        ])
        ->withTimestamps();
    }

    /**
     * Verifica se o usuário é Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        // 1. Checa por coluna de status
        if ($this->status === 'super_admin' || $this->status === 'super_adm') {
            return true;
        }

        // 2. Checa se possui a permissão 'super.adm' ou wildcard '*'
        return $this->hasPermissionTo('super.adm') || $this->hasPermissionTo('*');
    }

    /**
     * Verifica se o usuário tem determinada permissão (slug).
     */
    public function hasPermissionTo(string $permissionSlug): bool
    {
        // 1. Checa no campo de array JSON ('permissions')
        if (is_array($this->permissions) && in_array($permissionSlug, $this->permissions, true)) {
            return true;
        }

        // 2. Checa na tabela pivot via relacionamento ('permissionRelations')
        return $this->permissionRelations->contains('slug', $permissionSlug);
    }
}
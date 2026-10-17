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
     * Adiciona uma permissão ao array permissions.
     *
     * Retorna false se a permissão já existir.
     */
    public function addPermission(string $permissionSlug): bool
    {
        $permissions = $this->permissions ?? [];

        if (in_array($permissionSlug, $permissions, true)) {
            return false;
        }

        $permissions[] = $permissionSlug;

        $this->permissions = array_values($permissions);

        return $this->save();
    }

    /**
     * Remove uma permissão do array permissions.
     *
     * Retorna false se a permissão não existir no array.
     */
    public function removePermission(string $permissionSlug): bool
    {
        $permissions = $this->permissions ?? [];

        $filteredPermissions = array_values(
            array_filter(
                $permissions,
                static fn (string $permission): bool => $permission !== $permissionSlug
            )
        );

        if (count($filteredPermissions) === count($permissions)) {
            return false;
        }

        $this->permissions = $filteredPermissions;

        return $this->save();
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

    /**
     * Verifica se o array permissions contém a permissão super.adm.
     */
    public function hasSuperAdminPermissionInArray(): bool
    {
        return $this->hasPermissionInArray('super.adm');
    }

    /**
     * Verifica se o array permissions contém a permissão simple.adm.
     */
    public function hasSimpleAdminPermissionInArray(): bool
    {
        return $this->hasPermissionInArray('simple.adm');
    }

    /**
     * Verifica se uma permissão específica existe no array permissions.
     */
    public function hasPermissionInArray(string $permissionSlug): bool
    {
        return is_array($this->permissions)
            && in_array($permissionSlug, $this->permissions, true);
    }

    /**
     * Verifica se o usuário possui alguma permissão administrativa no array.
     */
    public function isAdminInArray(): bool
    {
        return $this->hasSuperAdminPermissionInArray()
            || $this->hasSimpleAdminPermissionInArray()
            || $this->hasPermissionInArray('*');
    }

    /**
     * Verifica se o usuário é algum tipo de administrador.
     *
     * Considera o status, o array permissions e a tabela pivot,
     * conforme as regras dos métodos existentes.
     */
    public function isAdmin(): bool
    {
        return $this->isSuperAdmin()
            || $this->hasPermissionTo('simple.adm');
    }

}
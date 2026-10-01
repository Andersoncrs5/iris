<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $full_name
 * @property string $email
 * @property string $password
 * @property string $status
 * @property array<string>|null $permissions
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property int $version
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class UserModel extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Tabela associada ao Model.
     *
     * @var string
     */
    protected $table = 'users';

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
        'full_name',
        'email',
        'password',
        'status',
        'permissions', // <--- ADICIONADO AO FILLABLE
        'email_verified_at',
        'last_login_at',
        'version',
    ];

    /**
     * Atributos ocultos nas respostas JSON/Serializações.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'permissions' => 'array', 
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'version' => 'integer',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            PermissionModel::class,
            'permission_user',
            'user_id',
            'permission_id'
        )
        ->withPivot(['id', 'version'])
        ->withTimestamps();
    }
}
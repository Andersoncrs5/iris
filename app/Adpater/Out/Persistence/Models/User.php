<?php

namespace App\Adapter\Out\Persistence\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'version' => 'integer',
        ];
    }
}
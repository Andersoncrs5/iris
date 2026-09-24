<?php

namespace App\Adapter\Out\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermissionRoleModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     *
     * @var string
     */
    protected $table = 'permission_role';

    /**
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $keyType = 'int';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'permission_id',
        'role_id',
        'version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'permission_id' => 'integer',
            'role_id' => 'integer',
            'version' => 'integer',
        ];
    }
}
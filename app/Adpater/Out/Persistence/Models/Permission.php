<?php

namespace App\Adapter\Out\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    /**
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'slug',
        'module',
        'description',
        'version',
    ];

    /**
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'version' => 'integer',
        ];
    }
}
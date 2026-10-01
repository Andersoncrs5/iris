<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $color
 * @property string|null $type
 * @property string|null $description
 * @property int $usage_count
 * @property bool $is_active
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class TagModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Tabela associada ao Model.
     *
     * @var string
     */
    protected $table = 'tags';

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
        'slug',
        'color',
        'type',
        'description',
        'usage_count',
        'is_active',
        'version',
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
            'usage_count' => 'integer',
            'is_active' => 'boolean',
            'version' => 'integer',
        ];
    }
}
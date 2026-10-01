<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $parent_id
 * @property string|null $icon
 * @property int $display_order
 * @property bool $is_active
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read CategoryModel|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CategoryModel> $children
 */
class CategoryModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'categories';

    /**
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
        'name',
        'slug',
        'description',
        'parent_id',
        'icon',
        'display_order',
        'is_active',
        'version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'parent_id' => 'integer',
            'display_order' => 'integer',
            'is_active' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
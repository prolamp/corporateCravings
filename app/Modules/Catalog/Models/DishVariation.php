<?php

namespace App\Modules\Catalog\Models;

use App\Support\Enums\VariationType;
use Database\Factories\DishVariationFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $dish_id
 * @property string $label
 * @property string $quantity
 * @property string $unit
 * @property string $price
 * @property VariationType $type
 * @property string $status
 * @property int $sort_order
 */
#[UseFactory(DishVariationFactory::class)]
class DishVariation extends Model
{
    /** @use HasFactory<DishVariationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'dish_id',
        'label',
        'quantity',
        'unit',
        'price',
        'type',
        'status',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'price' => 'decimal:2',
            'type' => VariationType::class,
        ];
    }

    /**
     * Only single servings may be added to the cart.
     */
    public function isCartable(): bool
    {
        return $this->type->isCartable() && $this->status === 'active';
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeSingle(Builder $query): void
    {
        $query->where('type', VariationType::Single);
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeBulk(Builder $query): void
    {
        $query->where('type', VariationType::Bulk);
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * @return BelongsTo<Dish, $this>
     */
    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }
}

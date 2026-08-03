<?php

namespace App\Modules\Packages\Models;

use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageItem extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'package_id',
        'dish_id',
        'dish_variation_id',
        'unit_price',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Package, $this>
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * @return BelongsTo<Dish, $this>
     */
    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    /**
     * @return BelongsTo<DishVariation, $this>
     */
    public function dishVariation(): BelongsTo
    {
        return $this->belongsTo(DishVariation::class);
    }
}

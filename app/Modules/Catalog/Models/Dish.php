<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Addons\Models\Addon;
use App\Modules\Packages\Models\PackageItem;
use App\Support\Enums\VariationType;
use Database\Factories\DishFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(DishFactory::class)]
class Dish extends Model
{
    /** @use HasFactory<DishFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'short_description',
        'image',
        'status',
        'sort_order',
    ];

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<DishVariation, $this>
     */
    public function variations(): HasMany
    {
        return $this->hasMany(DishVariation::class)->orderBy('sort_order');
    }

    /**
     * Cartable single servings shown on the menu.
     *
     * @return HasMany<DishVariation, $this>
     */
    public function singleVariations(): HasMany
    {
        return $this->variations()->where('type', VariationType::Single);
    }

    /**
     * Bulk tiers shown on the bulk page and quoted over WhatsApp.
     *
     * @return HasMany<DishVariation, $this>
     */
    public function bulkVariations(): HasMany
    {
        return $this->variations()->where('type', VariationType::Bulk);
    }

    /**
     * @return BelongsToMany<Addon, $this>
     */
    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'dish_addon')->withTimestamps();
    }

    /**
     * @return HasMany<PackageItem, $this>
     */
    public function packageItems(): HasMany
    {
        return $this->hasMany(PackageItem::class);
    }
}

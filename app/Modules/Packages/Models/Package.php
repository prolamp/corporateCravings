<?php

namespace App\Modules\Packages\Models;

use App\Modules\Catalog\Models\Dish;
use App\Modules\Subscribers\Models\SubscribeContract;
use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(PackageFactory::class)]
class Package extends Model
{
    /** @use HasFactory<PackageFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'image',
        'base_amount_per_person',
        'discount_amount',
        'discount_percent',
        'total_amount_per_person',
        'status',
        'show_on_website',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_amount_per_person' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'total_amount_per_person' => 'decimal:2',
            'show_on_website' => 'bool',
        ];
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Active packages that are listed on the public website.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeVisibleOnWebsite(Builder $query): void
    {
        $query->active()->where('show_on_website', true);
    }

    /**
     * @return HasMany<PackageItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PackageItem::class)->orderBy('sort_order');
    }

    /**
     * @return BelongsToMany<Dish, $this>
     */
    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'package_items')
            ->withPivot(['dish_variation_id', 'unit_price', 'sort_order'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<SubscribeContract, $this>
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(SubscribeContract::class);
    }
}

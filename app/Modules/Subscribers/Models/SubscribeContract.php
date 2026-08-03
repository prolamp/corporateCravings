<?php

namespace App\Modules\Subscribers\Models;

use App\Models\User;
use App\Modules\Orders\Models\Order;
use App\Modules\Packages\Models\Package;
use Database\Factories\SubscribeContractFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Contract for a user with the subscriber role.
 * Company/contact fields live on the related User — not duplicated here.
 */
#[UseFactory(SubscribeContractFactory::class)]
class SubscribeContract extends Model
{
    /** @use HasFactory<SubscribeContractFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'subscribe_contracts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'package_id',
        'meal_type',
        'days_of_week',
        'persons',
        'delivery_time',
        'package_amount',
        'discount',
        'agreed_total',
        'status',
        'starts_on',
        'ends_on',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'days_of_week' => 'array',
            'package_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'agreed_total' => 'decimal:2',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Package, $this>
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * @return HasMany<SubscriberSchedule, $this>
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(SubscriberSchedule::class, 'subscribe_contract_id');
    }

    /**
     * @return HasMany<SubscriberPackageHistory, $this>
     */
    public function packageHistory(): HasMany
    {
        return $this->hasMany(SubscriberPackageHistory::class, 'subscribe_contract_id');
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'subscribe_contract_id');
    }

    /**
     * @return HasMany<SubscriberBillSlip, $this>
     */
    public function billSlips(): HasMany
    {
        return $this->hasMany(SubscriberBillSlip::class, 'subscribe_contract_id');
    }
}

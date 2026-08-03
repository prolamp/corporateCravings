<?php

namespace App\Modules\Orders\Models;

use App\Models\User;
use App\Modules\Delivery\Models\DeliveryZone;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Support\Enums\OrderChannel;
use App\Support\Enums\OrderType;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(OrderFactory::class)]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Public/checkout-safe fields only. amount_paid is set explicitly by admin services.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'type',
        'channel',
        'status',
        'payment_method',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'delivery_zone_id',
        'delivery_fee',
        'subtotal',
        'list_amount',
        'discount_amount',
        'discount_percent',
        'quoted_amount',
        'subscribe_contract_id',
        'subscriber_schedule_id',
        'client_platform',
        'app_version',
        'user_agent',
        'notes',
        'delivery_person_name',
        'placed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => OrderType::class,
            'channel' => OrderChannel::class,
            'delivery_fee' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'list_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'quoted_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'placed_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeForPhone(Builder $query, string $phone): void
    {
        $query->where('customer_phone', $phone);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<DeliveryZone, $this>
     */
    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    /**
     * @return BelongsTo<SubscribeContract, $this>
     */
    public function subscribeContract(): BelongsTo
    {
        return $this->belongsTo(SubscribeContract::class);
    }

    /**
     * @return BelongsTo<SubscriberSchedule, $this>
     */
    public function subscriberSchedule(): BelongsTo
    {
        return $this->belongsTo(SubscriberSchedule::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}

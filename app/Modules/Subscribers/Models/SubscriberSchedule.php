<?php

namespace App\Modules\Subscribers\Models;

use App\Modules\Catalog\Models\Dish;
use App\Modules\Orders\Models\Order;
use App\Modules\Packages\Models\Package;
use Database\Factories\SubscriberScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(SubscriberScheduleFactory::class)]
class SubscriberSchedule extends Model
{
    /** @use HasFactory<SubscriberScheduleFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'subscribe_contract_id',
        'package_id',
        'dish_id',
        'date',
        'week_of_month',
        'dish_title',
        'package_title',
        'persons',
        'unit_price',
        'total',
        'status',
        'payment_status',
        'subscriber_bill_slip_id',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<SubscribeContract, $this>
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(SubscribeContract::class, 'subscribe_contract_id');
    }

    /**
     * @return BelongsTo<Dish, $this>
     */
    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    /**
     * @return BelongsTo<Package, $this>
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * @return BelongsTo<SubscriberBillSlip, $this>
     */
    public function billSlip(): BelongsTo
    {
        return $this->belongsTo(SubscriberBillSlip::class, 'subscriber_bill_slip_id');
    }

    /**
     * @return HasOne<Order, $this>
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'subscriber_schedule_id');
    }
}

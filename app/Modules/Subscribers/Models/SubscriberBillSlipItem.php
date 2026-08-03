<?php

namespace App\Modules\Subscribers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriberBillSlipItem extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'subscriber_bill_slip_id',
        'subscriber_schedule_id',
        'date',
        'dish_title',
        'persons',
        'unit_price',
        'line_total',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<SubscriberBillSlip, $this>
     */
    public function slip(): BelongsTo
    {
        return $this->belongsTo(SubscriberBillSlip::class, 'subscriber_bill_slip_id');
    }

    /**
     * @return BelongsTo<SubscriberSchedule, $this>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(SubscriberSchedule::class, 'subscriber_schedule_id');
    }
}

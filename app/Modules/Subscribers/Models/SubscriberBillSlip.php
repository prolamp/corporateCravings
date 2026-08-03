<?php

namespace App\Modules\Subscribers\Models;

use App\Models\User;
use Database\Factories\SubscriberBillSlipFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(SubscriberBillSlipFactory::class)]
class SubscriberBillSlip extends Model
{
    /** @use HasFactory<SubscriberBillSlipFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'subscribe_contract_id',
        'slip_number',
        'period_start',
        'period_end',
        'subtotal',
        'adjustment',
        'total',
        'status',
        'paid_at',
        'paid_by',
        'notes',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'subtotal' => 'decimal:2',
            'adjustment' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
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
     * @return HasMany<SubscriberBillSlipItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SubscriberBillSlipItem::class, 'subscriber_bill_slip_id');
    }

    /**
     * @return HasMany<SubscriberSchedule, $this>
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(SubscriberSchedule::class, 'subscriber_bill_slip_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function paidByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'issued'], true);
    }
}

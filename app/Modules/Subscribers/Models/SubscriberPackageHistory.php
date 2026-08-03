<?php

namespace App\Modules\Subscribers\Models;

use App\Modules\Packages\Models\Package;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriberPackageHistory extends Model
{
    /**
     * @var string
     */
    protected $table = 'subscriber_package_history';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'subscribe_contract_id',
        'package_id',
        'package_title',
        'package_amount',
        'discount',
        'agreed_total',
        'persons',
        'effective_from',
        'effective_to',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'package_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'agreed_total' => 'decimal:2',
            'effective_from' => 'datetime',
            'effective_to' => 'datetime',
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
     * @return BelongsTo<Package, $this>
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}

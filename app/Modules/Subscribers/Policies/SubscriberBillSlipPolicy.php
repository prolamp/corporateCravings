<?php

namespace App\Modules\Subscribers\Policies;

use App\Models\User;
use App\Modules\Subscribers\Models\SubscriberBillSlip;

class SubscriberBillSlipPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('bill_slips.view') || $user->can('bill_slips.manage');
    }

    public function view(User $user, SubscriberBillSlip $slip): bool
    {
        if ($user->can('bill_slips.view') || $user->can('bill_slips.manage')) {
            return true;
        }

        if (! $user->can('portal.subscriber.bills')) {
            return false;
        }

        $slip->loadMissing('contract');

        return (int) $slip->contract?->user_id === (int) $user->id
            && in_array($slip->status, ['issued', 'paid'], true);
    }

    public function create(User $user): bool
    {
        return $user->can('bill_slips.manage');
    }

    public function update(User $user, SubscriberBillSlip $slip): bool
    {
        return $user->can('bill_slips.manage');
    }

    public function markPaid(User $user, SubscriberBillSlip $slip): bool
    {
        return $user->can('bill_slips.manage');
    }

    public function cancel(User $user, SubscriberBillSlip $slip): bool
    {
        return $user->can('bill_slips.manage');
    }
}

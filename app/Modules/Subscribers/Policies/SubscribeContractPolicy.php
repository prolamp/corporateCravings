<?php

namespace App\Modules\Subscribers\Policies;

use App\Models\User;
use App\Modules\Subscribers\Models\SubscribeContract;

class SubscribeContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('contracts.manage');
    }

    public function view(User $user, SubscribeContract $contract): bool
    {
        return $user->can('contracts.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('contracts.manage');
    }

    public function update(User $user, SubscribeContract $contract): bool
    {
        return $user->can('contracts.manage');
    }

    public function delete(User $user, SubscribeContract $contract): bool
    {
        return $user->can('contracts.manage');
    }
}

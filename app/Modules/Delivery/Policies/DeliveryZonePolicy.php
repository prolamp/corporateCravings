<?php

namespace App\Modules\Delivery\Policies;

use App\Models\User;
use App\Modules\Delivery\Models\DeliveryZone;

class DeliveryZonePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('delivery_zones.manage');
    }

    public function view(User $user, DeliveryZone $zone): bool
    {
        return $user->can('delivery_zones.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('delivery_zones.manage');
    }

    public function update(User $user, DeliveryZone $zone): bool
    {
        return $user->can('delivery_zones.manage');
    }

    public function delete(User $user, DeliveryZone $zone): bool
    {
        return $user->can('delivery_zones.manage');
    }
}

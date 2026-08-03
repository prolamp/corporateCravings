<?php

namespace App\Modules\Orders\Policies;

use App\Models\User;
use App\Modules\Orders\Models\Order;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('orders.view');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can('orders.view');
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->can('orders.update_status');
    }

    public function create(User $user): bool
    {
        return $user->can('orders.manage');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('orders.manage');
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can('orders.manage');
    }
}

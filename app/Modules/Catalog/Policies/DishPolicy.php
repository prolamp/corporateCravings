<?php

namespace App\Modules\Catalog\Policies;

use App\Models\User;
use App\Modules\Catalog\Models\Dish;

class DishPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('dishes.manage');
    }

    public function view(User $user, Dish $dish): bool
    {
        return $user->can('dishes.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('dishes.manage');
    }

    public function update(User $user, Dish $dish): bool
    {
        return $user->can('dishes.manage');
    }

    public function delete(User $user, Dish $dish): bool
    {
        return $user->can('dishes.manage');
    }
}

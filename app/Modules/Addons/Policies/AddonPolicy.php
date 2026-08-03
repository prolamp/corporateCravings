<?php

namespace App\Modules\Addons\Policies;

use App\Models\User;
use App\Modules\Addons\Models\Addon;

class AddonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('addons.manage');
    }

    public function view(User $user, Addon $addon): bool
    {
        return $user->can('addons.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('addons.manage');
    }

    public function update(User $user, Addon $addon): bool
    {
        return $user->can('addons.manage');
    }

    public function delete(User $user, Addon $addon): bool
    {
        return $user->can('addons.manage');
    }
}

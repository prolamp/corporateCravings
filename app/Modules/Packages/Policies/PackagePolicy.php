<?php

namespace App\Modules\Packages\Policies;

use App\Models\User;
use App\Modules\Packages\Models\Package;

class PackagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('packages.manage');
    }

    public function view(User $user, Package $package): bool
    {
        return $user->can('packages.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('packages.manage');
    }

    public function update(User $user, Package $package): bool
    {
        return $user->can('packages.manage');
    }

    public function delete(User $user, Package $package): bool
    {
        return $user->can('packages.manage');
    }
}

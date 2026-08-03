<?php

namespace App\Modules\Cms\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function view(User $user, Page $page): bool
    {
        return $user->can('cms.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function update(User $user, Page $page): bool
    {
        return $user->can('cms.manage');
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->can('cms.manage');
    }
}

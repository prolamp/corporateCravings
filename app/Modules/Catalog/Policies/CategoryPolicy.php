<?php

namespace App\Modules\Catalog\Policies;

use App\Models\User;
use App\Modules\Catalog\Models\Category;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('categories.manage');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->can('categories.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('categories.manage');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can('categories.manage');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->can('categories.manage');
    }
}

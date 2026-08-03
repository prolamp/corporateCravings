<?php

namespace App\Modules\Expenses\Policies;

use App\Models\User;
use App\Modules\Expenses\Models\ExpenseCategory;

class ExpenseCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('expenses.manage');
    }

    public function view(User $user, ExpenseCategory $category): bool
    {
        return $user->can('expenses.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('expenses.manage');
    }

    public function update(User $user, ExpenseCategory $category): bool
    {
        return $user->can('expenses.manage');
    }

    public function delete(User $user, ExpenseCategory $category): bool
    {
        return $user->can('expenses.manage');
    }
}

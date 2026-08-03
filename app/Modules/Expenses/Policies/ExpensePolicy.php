<?php

namespace App\Modules\Expenses\Policies;

use App\Models\User;
use App\Modules\Expenses\Models\Expense;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('expenses.view');
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->can('expenses.view');
    }

    public function create(User $user): bool
    {
        return $user->can('expenses.create');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->can('expenses.manage');
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->can('expenses.manage');
    }

    public function updateStatus(User $user, Expense $expense): bool
    {
        return $user->can('expenses.manage');
    }
}

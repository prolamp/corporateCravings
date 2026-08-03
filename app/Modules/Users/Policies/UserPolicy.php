<?php

namespace App\Modules\Users\Policies;

use App\Models\User;
use App\Modules\Users\Services\UserService;
use App\Support\Enums\UserRole;
use App\Support\RoleAccess;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('users.view');
    }

    public function view(User $actor, User $user): bool
    {
        if (! $actor->can('users.view')) {
            return false;
        }

        $actorRole = $actor->primaryRole();
        $targetRole = $user->primaryRole();

        if (
            $actorRole instanceof UserRole
            && $targetRole instanceof UserRole
            && $targetRole->isAbove($actorRole)
        ) {
            return false;
        }

        return true;
    }

    public function create(User $actor): bool
    {
        return $actor->can('users.view') && count(RoleAccess::creatableRoles($actor)) > 0;
    }

    public function update(User $actor, User $user): bool
    {
        if (! $actor->can('users.update')) {
            return false;
        }

        if ($actor->is($user)) {
            return true;
        }

        return app(UserService::class)->actorCanManage($actor, $user);
    }

    public function delete(User $actor, User $user): bool
    {
        if (! $actor->can('users.delete')) {
            return false;
        }

        if ($actor->is($user)) {
            return false;
        }

        $targetRole = $user->primaryRole();

        if ($targetRole === UserRole::SuperAdmin) {
            return $actor->isSuperAdmin();
        }

        return app(UserService::class)->actorCanManage($actor, $user);
    }
}

<?php

namespace App\Modules\Subscribers\Policies;

use App\Models\User;
use App\Modules\Users\Services\UserService;
use App\Support\Enums\UserRole;
use App\Support\RoleAccess;

class SubscriberPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('contracts.manage');
    }

    public function view(User $actor, User $subscriber): bool
    {
        if (! $actor->can('contracts.manage')) {
            return false;
        }

        return $subscriber->hasRole(UserRole::Subscriber->value);
    }

    public function create(User $actor): bool
    {
        return $actor->can('users.create_subscriber')
            && RoleAccess::canCreateRole($actor, UserRole::Subscriber);
    }

    public function update(User $actor, User $subscriber): bool
    {
        if (! $actor->can('users.update')) {
            return false;
        }

        if (! $subscriber->hasRole(UserRole::Subscriber->value)) {
            return false;
        }

        return app(UserService::class)->actorCanManage($actor, $subscriber);
    }

    public function delete(User $actor, User $subscriber): bool
    {
        if (! $actor->can('users.delete')) {
            return false;
        }

        if (! $subscriber->hasRole(UserRole::Subscriber->value)) {
            return false;
        }

        return app(UserService::class)->actorCanManage($actor, $subscriber);
    }
}

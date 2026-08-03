<?php

namespace App\Modules\Subscribers\Policies;

use App\Models\User;
use App\Modules\Subscribers\Models\SubscriberSchedule;

class SubscriberSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('schedules.manage');
    }

    public function view(User $user, SubscriberSchedule $schedule): bool
    {
        return $user->can('schedules.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('schedules.manage');
    }

    public function update(User $user, SubscriberSchedule $schedule): bool
    {
        return $user->can('schedules.manage');
    }

    public function delete(User $user, SubscriberSchedule $schedule): bool
    {
        return $user->can('schedules.manage');
    }
}

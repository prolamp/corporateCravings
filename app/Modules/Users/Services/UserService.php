<?php

namespace App\Modules\Users\Services;

use App\Models\User;
use App\Support\Enums\UserRole;
use App\Support\ImageService;
use App\Support\RoleAccess;
use App\Support\StatusRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserService
{
    public function __construct(
        private readonly ImageService $images,
    ) {}

    /**
     * @param  array{search?: string|null, role?: string|null, status?: string|null}  $filters
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(User $actor, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query()
            ->with('roles')
            ->whereKeyNot($actor->id)
            ->latest('id');

        $actorRole = $actor->primaryRole();
        if ($actorRole instanceof UserRole) {
            $aboveRoles = $actorRole->aboveRoleNames();
            if ($aboveRoles !== []) {
                $query->whereDoesntHave('roles', function ($q) use ($aboveRoles): void {
                    $q->whereIn('name', $aboveRoles);
                });
            }
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['role'])) {
            $query->role($filters['role']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{
     *     name: string,
     *     email?: string|null,
     *     phone: string,
     *     password?: string|null,
     *     address?: string|null,
     *     company_name?: string|null,
     *     status: string,
     *     role: string,
     *     image?: UploadedFile|null,
     * }  $data
     */
    public function create(User $actor, array $data): User
    {
        $role = UserRole::from($data['role']);

        if (! RoleAccess::canCreateRole($actor, $role)) {
            throw new InvalidArgumentException('You are not allowed to create this role.');
        }

        return DB::transaction(function () use ($data, $role): User {
            $password = ($data['password'] ?? null) ?: $data['phone'];

            $user = new User([
                'name' => $data['name'],
                'email' => ($data['email'] ?? null) ?: null,
                'phone' => $data['phone'],
                'password' => $password,
                'address' => $data['address'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'status' => $data['status'],
            ]);

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $user->image = $this->images->store($data['image'], 'users');
            }

            $user->save();
            $user->syncRoles([$role->value]);
            $user->forceFill(['email_verified_at' => now()])->save();

            return $user->load('roles');
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     email?: string|null,
     *     phone: string,
     *     password?: string|null,
     *     address?: string|null,
     *     company_name?: string|null,
     *     status: string,
     *     role: string,
     *     image?: UploadedFile|null,
     *     remove_image?: bool,
     * }  $data
     */
    public function update(User $actor, User $user, array $data): User
    {
        $role = UserRole::from($data['role']);

        if (! $this->actorCanManage($actor, $user)) {
            throw new InvalidArgumentException('You are not allowed to update this user.');
        }

        $currentRole = $user->primaryRole();
        if ($currentRole !== $role && ! RoleAccess::canCreateRole($actor, $role)) {
            throw new InvalidArgumentException('You are not allowed to assign this role.');
        }

        return DB::transaction(function () use ($user, $data, $role): User {
            $user->fill([
                'name' => $data['name'],
                'email' => ($data['email'] ?? null) ?: null,
                'phone' => $data['phone'],
                'address' => $data['address'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'status' => $data['status'],
            ]);

            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }

            if (! empty($data['remove_image'])) {
                $this->images->delete($user->image);
                $user->image = null;
            }

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $user->image = $this->images->store($data['image'], 'users', $user->image);
            }

            $user->save();
            $user->syncRoles([$role->value]);

            return $user->load('roles');
        });
    }

    public function delete(User $actor, User $user): void
    {
        if ($actor->is($user)) {
            throw new InvalidArgumentException('You cannot delete your own account.');
        }

        if (! $this->actorCanManage($actor, $user)) {
            throw new InvalidArgumentException('You are not allowed to delete this user.');
        }

        DB::transaction(function () use ($user): void {
            $this->images->delete($user->image);
            $user->syncRoles([]);
            $user->delete();
        });
    }

    /**
     * Toggle between active and inactive. Suspended users become active when turned on.
     */
    public function toggleActive(User $actor, User $user): User
    {
        if (! $this->actorCanManage($actor, $user) && ! $actor->is($user)) {
            throw new InvalidArgumentException('You are not allowed to update this user.');
        }

        if ($actor->is($user) && ! $actor->can('users.update')) {
            throw new InvalidArgumentException('You are not allowed to update this user.');
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return $user->load('roles');
    }

    public function actorCanManage(User $actor, User $target): bool
    {
        if ($actor->is($target)) {
            return $actor->can('users.update');
        }

        $targetRole = $target->primaryRole();

        if (! $targetRole instanceof UserRole) {
            return $actor->can('users.update');
        }

        if ($targetRole === UserRole::SuperAdmin) {
            return $actor->isSuperAdmin() && ! $actor->is($target);
        }

        return RoleAccess::canCreateRole($actor, $targetRole);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $user, ?User $actor = null): array
    {
        $role = $user->primaryRole();
        $status = StatusRegistry::get('user', $user->status);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'company_name' => $user->company_name,
            'image' => $user->image,
            'image_url' => $this->images->url($user->image),
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'role' => $role?->value,
            'role_label' => $role?->label(),
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
            'can_update' => $actor ? $actor->can('update', $user) : false,
            'can_delete' => $actor ? $actor->can('delete', $user) : false,
        ];
    }
}

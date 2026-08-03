<?php

namespace App\Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Users\Http\Requests\StoreUserRequest;
use App\Modules\Users\Http\Requests\UpdateUserRequest;
use App\Modules\Users\Services\UserService;
use App\Support\Enums\UserRole;
use App\Support\RoleAccess;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $users,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'role' => $request->string('role')->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ];

        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        $paginator = $this->users->paginate($actor, $filters);

        return Inertia::render('admin/users/index', [
            'users' => [
                'data' => $paginator->getCollection()
                    ->map(fn (User $user) => $this->users->toArray($user, $actor))
                    ->values()
                    ->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url(max($paginator->lastPage(), 1)),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ],
            'filters' => $filters,
            'roleOptions' => $this->roleFilterOptions($actor),
            'statusOptions' => $this->statusOptions(),
            'canCreate' => $actor->can('create', User::class),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('admin/users/create', [
            'roleOptions' => $this->creatableRoleOptions($request->user()),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        try {
            $user = $this->users->create($request->user(), $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', true)
            ->with('message', 'User created successfully.')
            ->with('code', 'user_created');
    }

    public function show(Request $request, User $user): Response
    {
        $this->authorize('view', $user);

        return Inertia::render('admin/users/show', [
            'user' => $this->users->toArray($user->load('roles'), $request->user()),
            'canUpdate' => $request->user()?->can('update', $user) ?? false,
            'canDelete' => $request->user()?->can('delete', $user) ?? false,
        ]);
    }

    public function edit(Request $request, User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('admin/users/edit', [
            'user' => $this->users->toArray($user->load('roles'), $request->user()),
            'roleOptions' => $this->editableRoleOptions($request->user(), $user),
            'statusOptions' => $this->statusOptions(),
            'canDelete' => $request->user()?->can('delete', $user) ?? false,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        try {
            $this->users->update($request->user(), $user, $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', true)
            ->with('message', 'User updated successfully.')
            ->with('code', 'user_updated');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        try {
            $this->users->delete($request->user(), $user);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', true)
            ->with('message', 'User deleted successfully.')
            ->with('code', 'user_deleted');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        try {
            $updated = $this->users->toggleActive($request->user(), $user);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $label = $updated->status === 'active' ? 'activated' : 'deactivated';

        return back()
            ->with('success', true)
            ->with('message', "User {$label} successfully.")
            ->with('code', 'user_status_toggled');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function creatableRoleOptions(?User $actor): array
    {
        if (! $actor) {
            return [];
        }

        return array_map(
            fn (UserRole $role) => ['value' => $role->value, 'label' => $role->label()],
            RoleAccess::creatableRoles($actor),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function editableRoleOptions(User $actor, User $target): array
    {
        $options = $this->creatableRoleOptions($actor);
        $current = $target->primaryRole();

        if ($current && ! collect($options)->contains('value', $current->value)) {
            $options[] = ['value' => $current->value, 'label' => $current->label()];
        }

        return $options;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function roleFilterOptions(User $actor): array
    {
        $roles = $actor->primaryRole()?->atOrBelow() ?? UserRole::cases();

        return array_map(
            fn (UserRole $role) => ['value' => $role->value, 'label' => $role->label()],
            $roles,
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            fn (array $status) => [
                'value' => $status['code'],
                'label' => $status['label'],
            ],
            StatusRegistry::all('user'),
        );
    }
}

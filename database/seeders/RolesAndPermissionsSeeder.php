<?php

namespace Database\Seeders;

use App\Support\Enums\UserRole;
use App\Support\RoleAccess;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        foreach (RoleAccess::allPermissionNames() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Seeders may run with model events disabled, so Spatie's cache is not
        // auto-invalidated after the inserts above.
        $registrar->forgetCachedPermissions();

        foreach (UserRole::cases() as $roleEnum) {
            $role = Role::findOrCreate($roleEnum->value, 'web');
            $role->syncPermissions(RoleAccess::permissionsForRole($roleEnum));
        }
    }
}

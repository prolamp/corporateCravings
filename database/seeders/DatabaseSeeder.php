<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Enums\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@corporatecraving.com'],
            [
                'name' => 'Super Admin',
                'phone' => '03000000000',
                'password' => Hash::make('Apple12345'),
                'status' => 'active',
            ],
        );
        $admin->syncRoles([UserRole::SuperAdmin->value]);

        $this->call([
            CatalogSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}

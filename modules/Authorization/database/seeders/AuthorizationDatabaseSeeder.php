<?php

namespace Modules\Authorization\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;
use Modules\Authorization\Enums\Role as RoleEnum;
use Modules\User\Models\User;

class AuthorizationDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->runOnDevelopment();
    }

    protected function runOnDevelopment(): void
    {
        if (!app()->environment('local')) {
            return;
        }

        $roles = [];
        foreach (RoleEnum::cases() as $role) {
            $roles[] = [
                'name'        => $role->value,
                'guard_name'  => 'web',
                'description' => Str::title($role->value).' Role',
            ];
        }
        $insertedRoles = Role::factory()->createMany($roles);
        $insertedPermissions = Permission::factory()->createMany([
            ['name' => 'access-dashboard', 'guard_name' => 'web', 'description' => 'Access Admin Dashboard'],
            ['name' => 'manage-content-types', 'guard_name' => 'web', 'description' => 'Manage Content Types'],
        ]);
        $insertedRoles->each(function (Role $role) use ($insertedPermissions) {
            if ($role->name === RoleEnum::GUEST->value) {
                return;
            }
            $role->givePermissionTo($insertedPermissions);
        });

        $admin = User::query()->where('username', 'admin')->first();
        $admin->assignRole(RoleEnum::ADMIN->value);

        $publisher = User::query()->where('username', 'publisher')->first();
        $publisher->assignRole(RoleEnum::PUBLISHER->value);

        $editor = User::query()->where('username', 'editor')->first();
        $editor->assignRole(RoleEnum::EDITOR->value);
    }
}

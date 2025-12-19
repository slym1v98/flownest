<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view-posts',
            'create-posts',
            'edit-posts',
            'delete-posts',
            'publish-posts',
            'manage-users',
            'manage-roles',
            'manage-media',
            'view-analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Editor role: can create and edit posts, but cannot publish
        $editor = Role::firstOrCreate(['name' => 'Editor']);
        $editor->givePermissionTo([
            'view-posts',
            'create-posts',
            'edit-posts',
            'manage-media',
        ]);

        // Publisher role: can publish posts and do everything an editor can
        $publisher = Role::firstOrCreate(['name' => 'Publisher']);
        $publisher->givePermissionTo([
            'view-posts',
            'create-posts',
            'edit-posts',
            'publish-posts',
            'manage-media',
        ]);

        // Admin role: can do everything
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo(Permission::all());

        $this->command->info('Roles and permissions seeded successfully!');
    }
}

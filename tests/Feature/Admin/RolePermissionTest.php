<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_roles_are_created_correctly(): void
    {
        $this->assertDatabaseHas('roles', ['name' => 'Editor']);
        $this->assertDatabaseHas('roles', ['name' => 'Publisher']);
        $this->assertDatabaseHas('roles', ['name' => 'Admin']);
    }

    public function test_permissions_are_created_correctly(): void
    {
        $expectedPermissions = [
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

        foreach ($expectedPermissions as $permission) {
            $this->assertDatabaseHas('permissions', ['name' => $permission]);
        }
    }

    public function test_editor_has_correct_permissions(): void
    {
        $editor = Role::findByName('Editor');

        $this->assertTrue($editor->hasPermissionTo('create-posts'));
        $this->assertTrue($editor->hasPermissionTo('edit-posts'));
        $this->assertFalse($editor->hasPermissionTo('publish-posts'));
        $this->assertFalse($editor->hasPermissionTo('manage-users'));
    }

    public function test_publisher_has_correct_permissions(): void
    {
        $publisher = Role::findByName('Publisher');

        $this->assertTrue($publisher->hasPermissionTo('create-posts'));
        $this->assertTrue($publisher->hasPermissionTo('edit-posts'));
        $this->assertTrue($publisher->hasPermissionTo('publish-posts'));
        $this->assertFalse($publisher->hasPermissionTo('manage-users'));
    }

    public function test_admin_has_all_permissions(): void
    {
        $admin = Role::findByName('Admin');
        $allPermissions = Permission::all();

        foreach ($allPermissions as $permission) {
            $this->assertTrue($admin->hasPermissionTo($permission));
        }
    }

    public function test_user_can_be_assigned_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Editor');

        $this->assertTrue($user->hasRole('Editor'));
        $this->assertTrue($user->can('create-posts'));
    }

    public function test_admin_can_assign_role_to_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->post(
            route('admin.users.assign-role', $user),
            ['role' => 'Editor']
        );

        $response->assertRedirect();
        $this->assertTrue($user->fresh()->hasRole('Editor'));
    }

    public function test_non_admin_cannot_assign_roles(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('Editor');

        $user = User::factory()->create();

        $response = $this->actingAs($editor)->post(
            route('admin.users.assign-role', $user),
            ['role' => 'Publisher']
        );

        $response->assertStatus(403);
    }

    public function test_admin_can_remove_role_from_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $user = User::factory()->create();
        $user->assignRole('Editor');

        $response = $this->actingAs($admin)->post(
            route('admin.users.remove-role', $user),
            ['role' => 'Editor']
        );

        $response->assertRedirect();
        $this->assertFalse($user->fresh()->hasRole('Editor'));
    }
}

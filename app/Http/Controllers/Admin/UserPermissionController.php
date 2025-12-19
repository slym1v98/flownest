<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserPermissionController extends Controller
{
    /**
     * Display user permissions management page.
     */
    public function index(): Response
    {
        $users = User::with(['roles', 'permissions'])->get();
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return Inertia::render('admin/users/Permissions', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        if (! $request->user()->can('manage-roles')) {
            abort(403);
        }

        $user->assignRole($validated['role']);

        return redirect()->back()->with('success', 'Role assigned successfully.');
    }

    /**
     * Remove a role from a user.
     */
    public function removeRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        if (! $request->user()->can('manage-roles')) {
            abort(403);
        }

        $user->removeRole($validated['role']);

        return redirect()->back()->with('success', 'Role removed successfully.');
    }

    /**
     * Assign a permission directly to a user.
     */
    public function assignPermission(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'permission' => 'required|string|exists:permissions,name',
        ]);

        if (! $request->user()->can('manage-roles')) {
            abort(403);
        }

        $user->givePermissionTo($validated['permission']);

        return redirect()->back()->with('success', 'Permission assigned successfully.');
    }

    /**
     * Remove a permission from a user.
     */
    public function revokePermission(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'permission' => 'required|string|exists:permissions,name',
        ]);

        if (! $request->user()->can('manage-roles')) {
            abort(403);
        }

        $user->revokePermissionTo($validated['permission']);

        return redirect()->back()->with('success', 'Permission revoked successfully.');
    }
}

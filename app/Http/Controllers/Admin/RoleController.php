<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Support\AdminMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of admin roles.
     */
    public function index()
    {
        $roles = AdminRole::withCount('users')->ordered()->get();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        return view('admin.roles.create', [
            'role' => new AdminRole(['permissions' => []]),
            'menuGroups' => $this->assignableMenuGroups(),
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $this->validateRole($request);

        AdminRole::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'permissions' => $this->grantablePermissions($request->input('permissions', [])),
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully!');
    }

    /**
     * Show the form for editing a role.
     */
    public function edit(AdminRole $role)
    {
        if ($role->is_super_admin) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'The Super Admin role always has full access and cannot be edited.');
        }

        return view('admin.roles.edit', [
            'role' => $role,
            'menuGroups' => $this->assignableMenuGroups(),
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, AdminRole $role)
    {
        if ($role->is_super_admin) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'The Super Admin role always has full access and cannot be edited.');
        }

        $validated = $this->validateRole($request, $role);

        // Keep any permission the editor cannot grant themselves.
        $locked = array_diff($role->permissionKeys(), $this->assignableKeys());
        $permissions = array_values(array_unique(array_merge(
            $locked,
            $this->grantablePermissions($request->input('permissions', []))
        )));

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'permissions' => AdminMenu::filterKeys($permissions),
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully!');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(AdminRole $role)
    {
        if ($role->is_system) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'System roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'This role is still assigned to one or more users. Reassign them first.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully!');
    }

    /**
     * Shared validation rules for create and update.
     *
     * @return array<string, mixed>
     */
    protected function validateRole(Request $request, ?AdminRole $role = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('admin_roles', 'name')->ignore($role?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::in(AdminMenu::keys())],
        ]);
    }

    /**
     * Menu groups the current admin is allowed to hand out.
     *
     * @return array<int, array{label: string, items: array<int, array<string, mixed>>}>
     */
    protected function assignableMenuGroups(): array
    {
        $allowed = $this->assignableKeys();

        return collect(AdminMenu::groups())
            ->map(fn ($group) => [
                'label' => $group['label'],
                'items' => array_values(array_filter(
                    $group['items'],
                    fn ($item) => in_array($item['key'], $allowed, true)
                )),
            ])
            ->filter(fn ($group) => !empty($group['items']))
            ->values()
            ->all();
    }

    /**
     * An admin can only grant menus they can access themselves.
     *
     * @return array<int, string>
     */
    protected function assignableKeys(): array
    {
        return auth()->user()->accessibleMenuKeys();
    }

    /**
     * Strip out permissions the current admin is not allowed to grant.
     *
     * @param  array<int, mixed>  $permissions
     * @return array<int, string>
     */
    protected function grantablePermissions(array $permissions): array
    {
        return array_values(array_intersect(
            AdminMenu::filterKeys($permissions),
            $this->assignableKeys()
        ));
    }

    /**
     * Build a slug that does not collide with an existing role.
     */
    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'role';
        $slug = $base;
        $suffix = 2;

        while (AdminRole::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}

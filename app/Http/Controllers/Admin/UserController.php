<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query()->with('adminRoles');

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by admin role
        if ($request->filled('admin_role')) {
            $query->whereHas('adminRoles', fn ($q) => $q->where('admin_roles.id', $request->admin_role));
        }

        // Filter by status (we'll use email_verified_at as active/inactive indicator)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'adminRoles' => AdminRole::ordered()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create', [
            'adminRoles' => $this->assignableAdminRoles(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'role' => ['required', Rule::in(['user', 'admin'])],
            'admin_role_ids' => $this->adminRoleRules($request),
            'admin_role_ids.*' => [Rule::exists('admin_roles', 'id')],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ], [], ['admin_role_ids' => 'admin roles']);

        $data = $request->only(['name', 'email', 'role', 'phone', 'address']);
        $data['password'] = Hash::make($request->password);
        $data['email_verified_at'] = $request->boolean('is_active', true) ? now() : null;

        $user = User::create($data);
        $user->adminRoles()->sync($this->grantableRoleIds($request));

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'adminRoles' => $this->assignableAdminRoles($user),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(6)],
            'role' => ['required', Rule::in(['user', 'admin'])],
            'admin_role_ids' => $this->adminRoleRules($request),
            'admin_role_ids.*' => [Rule::exists('admin_roles', 'id')],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ], [], ['admin_role_ids' => 'admin roles']);

        $data = $request->only(['name', 'email', 'role', 'phone', 'address']);

        // Never let an admin lock themselves out by lowering their own access.
        $editingSelf = $user->id === auth()->id();

        if ($editingSelf) {
            unset($data['role']);
        }

        // Update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Update active status
        $data['email_verified_at'] = $request->boolean('is_active') ? ($user->email_verified_at ?? now()) : null;

        $user->update($data);

        if (!$editingSelf) {
            // Roles the editor cannot grant stay attached rather than being dropped.
            $locked = $user->adminRoles->pluck('id')
                ->diff($this->assignableAdminRoles($user)->pluck('id'));

            $user->adminRoles()->sync(
                $locked->merge($this->grantableRoleIds($request, $user))->unique()->all()
            );
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting the current admin user
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Toggle user status (active/inactive).
     */
    public function toggleStatus(User $user)
    {
        // Prevent deactivating the current admin user
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        $user->update([
            'email_verified_at' => $user->email_verified_at ? null : now()
        ]);

        $status = $user->email_verified_at ? 'activated' : 'deactivated';
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "User {$status} successfully!",
                'is_active' => (bool) $user->email_verified_at
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User {$status} successfully!");
    }

    /**
     * Admin roles the current admin may hand out.
     *
     * Only a super admin can grant unrestricted access; roles already on the
     * user stay selectable so an edit never silently drops them.
     */
    protected function assignableAdminRoles(?User $user = null)
    {
        $roles = AdminRole::ordered()->get();

        if (auth()->user()->isSuperAdmin()) {
            return $roles;
        }

        $held = $user?->adminRoles->pluck('id') ?? collect();

        return $roles->filter(
            fn (AdminRole $role) => !$role->is_super_admin || $held->contains($role->id)
        )->values();
    }

    /**
     * Validation rules for the admin role checkboxes.
     *
     * @return array<int, mixed>
     */
    protected function adminRoleRules(Request $request): array
    {
        if ($request->input('role') !== 'admin') {
            return ['nullable', 'array'];
        }

        // An admin with no role can only reach the dashboard, so require one.
        return ['required', 'array', 'min:1'];
    }

    /**
     * Role ids from the request, minus any the current admin cannot grant.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    protected function grantableRoleIds(Request $request, ?User $user = null)
    {
        if ($request->input('role') !== 'admin') {
            return collect();
        }

        $allowed = $this->assignableAdminRoles($user)->pluck('id');

        return collect($request->input('admin_role_ids', []))
            ->map(fn ($id) => (int) $id)
            ->intersect($allowed)
            ->values();
    }

    /**
     * Bulk update users status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'action' => ['required', Rule::in(['activate', 'deactivate'])],
        ]);

        // Prevent affecting the current admin user
        $userIds = collect($request->user_ids)->reject(function ($id) {
            return $id == auth()->id();
        })->values()->all();

        if (empty($userIds)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No valid users selected for bulk action.');
        }

        $emailVerifiedAt = $request->action === 'activate' ? now() : null;
        
        User::whereIn('id', $userIds)->update([
            'email_verified_at' => $emailVerifiedAt
        ]);

        $count = count($userIds);
        $action = $request->action === 'activate' ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.users.index')
            ->with('success', "{$count} users {$action} successfully!");
    }
}
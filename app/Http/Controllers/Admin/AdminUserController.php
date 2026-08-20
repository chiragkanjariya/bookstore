<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Admin accounts (role = admin). Customer accounts live in UserController.
 *
 * Status here is real: an admin whose email_verified_at is null is refused at
 * login by Auth\LoginController.
 */
class AdminUserController extends Controller
{
    /**
     * Route-model binding resolves any User by id, so every record-scoped
     * action has to re-check the role itself.
     */
    protected function guardScope(User $user): void
    {
        abort_unless($user->role === 'admin', 404);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'admin')->with('adminRoles');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if ($request->filled('admin_role')) {
            $query->whereHas('adminRoles', fn ($q) => $q->where('admin_roles.id', $request->admin_role));
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.admin-users.index', [
            'users' => $users,
            'adminRoles' => AdminRole::ordered()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.admin-users.create', [
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
            'admin_role_ids' => ['required', 'array', 'min:1'],
            'admin_role_ids.*' => [Rule::exists('admin_roles', 'id')],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ], [], ['admin_role_ids' => 'admin roles']);

        $data = $request->only(['name', 'email', 'phone', 'address']);
        $data['password'] = Hash::make($request->password);

        // The role is fixed by which screen you are on.
        $data['role'] = 'admin';
        $data['email_verified_at'] = $request->boolean('is_active', true) ? now() : null;

        $user = User::create($data);
        $user->adminRoles()->sync($this->grantableRoleIds($request));

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->guardScope($user);

        return view('admin.admin-users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->guardScope($user);

        return view('admin.admin-users.edit', [
            'user' => $user,
            'adminRoles' => $this->assignableAdminRoles($user),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->guardScope($user);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(6)],
            'role' => ['required', Rule::in(['user', 'admin'])],
            'admin_role_ids' => $this->adminRoleRules($request, $user),
            'admin_role_ids.*' => [Rule::exists('admin_roles', 'id')],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ], [], ['admin_role_ids' => 'admin roles']);

        $data = $request->only(['name', 'email', 'role', 'phone', 'address']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Never let an admin lock themselves out or demote themselves.
        $editingSelf = $user->id === auth()->id();

        if ($editingSelf) {
            $data['role'] = 'admin';
            $data['email_verified_at'] = $user->email_verified_at ?? now();
        } else {
            $data['email_verified_at'] = $request->boolean('is_active') ? ($user->email_verified_at ?? now()) : null;
        }

        $user->update($data);

        if (!$editingSelf) {
            // Roles the editor cannot grant stay attached rather than being
            // dropped, and a demotion clears everything grantable.
            $locked = $user->adminRoles->pluck('id')
                ->diff($this->assignableAdminRoles($user)->pluck('id'));

            $user->adminRoles()->sync(
                $locked->merge($this->grantableRoleIds($request, $user))->unique()->all()
            );
        }

        // A demoted admin disappears from this list, so follow them over.
        if ($user->role === 'user') {
            return redirect()->route('admin.users.index')
                ->with('success', 'Admin updated and moved to Manage Users.');
        }

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->guardScope($user);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.admin-users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin deleted successfully!');
    }

    /**
     * Toggle admin status (active/inactive).
     */
    public function toggleStatus(User $user)
    {
        $this->guardScope($user);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.admin-users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        $user->update([
            'email_verified_at' => $user->email_verified_at ? null : now()
        ]);

        $status = $user->email_verified_at ? 'activated' : 'deactivated';

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Admin {$status} successfully!",
                'is_active' => (bool) $user->email_verified_at
            ]);
        }

        return redirect()->route('admin.admin-users.index')
            ->with('success', "Admin {$status} successfully!");
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
    protected function adminRoleRules(Request $request, ?User $user = null): array
    {
        // A demoted account keeps no admin roles, and the form locks your own
        // roles, so neither case posts anything to require.
        if ($request->input('role') !== 'admin' || ($user && $user->id === auth()->id())) {
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
        if ($request->input('role', 'admin') !== 'admin') {
            return collect();
        }

        $allowed = $this->assignableAdminRoles($user)->pluck('id');

        return collect($request->input('admin_role_ids', []))
            ->map(fn ($id) => (int) $id)
            ->intersect($allowed)
            ->values();
    }

    /**
     * Bulk update admin status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'action' => ['required', Rule::in(['activate', 'deactivate'])],
        ]);

        // Re-scope to admins so a posted customer id cannot be flipped here,
        // and never touch the acting account.
        $userIds = User::whereIn('id', $request->user_ids)
            ->where('role', 'admin')
            ->where('id', '!=', auth()->id())
            ->pluck('id');

        if ($userIds->isEmpty()) {
            return redirect()->route('admin.admin-users.index')
                ->with('error', 'No valid admins selected for bulk action.');
        }

        User::whereIn('id', $userIds)->update([
            'email_verified_at' => $request->action === 'activate' ? now() : null
        ]);

        $count = $userIds->count();
        $action = $request->action === 'activate' ? 'activated' : 'deactivated';

        return redirect()->route('admin.admin-users.index')
            ->with('success', "{$count} admins {$action} successfully!");
    }
}

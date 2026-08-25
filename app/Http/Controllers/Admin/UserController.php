<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Customer accounts (role = user). Admin accounts live in AdminUserController,
 * which is also where admin roles are handed out.
 */
class UserController extends Controller
{
    /**
     * Route-model binding resolves any User by id, so every record-scoped
     * action has to re-check the role itself.
     */
    protected function guardScope(User $user): void
    {
        abort_unless($user->role === 'user', 404);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'user');

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
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
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $data = $request->only(['name', 'email', 'phone', 'address']);
        $data['password'] = Hash::make($request->password);

        // The role is fixed by which screen you are on.
        $data['role'] = 'user';

        // Customers are never gated on verification; stamping it here keeps
        // admin-created accounts from rendering as "Inactive" elsewhere.
        $data['email_verified_at'] = now();

        User::create($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->guardScope($user);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->guardScope($user);

        return view('admin.users.edit', compact('user'));
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
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $data = $request->only(['name', 'email', 'role', 'phone', 'address']);

        // Update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Promoting a customer to admin has to leave them able to log in —
        // admin login is gated on email_verified_at.
        if ($data['role'] === 'admin' && !$user->email_verified_at) {
            $data['email_verified_at'] = now();
        }

        $user->update($data);

        // A promoted user disappears from this list, so follow them over. They
        // arrive with no admin role, which is assigned on that screen.
        if ($user->role === 'admin') {
            return redirect()->route('admin.admin-users.index')
                ->with('success', 'User updated and moved to Admin Users. Assign an admin role to grant access.');
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->guardScope($user);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Toggle the active status of the specified user.
     */
    public function toggleStatus(User $user)
    {
        $this->guardScope($user);

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "User {$status} successfully!",
                'is_active' => $user->is_active
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User {$status} successfully!");
    }
}

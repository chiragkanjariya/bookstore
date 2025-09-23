<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => 'user', // Default role
        ]);

        // Log the user in
        Auth::login($user);
        
        // Update last login time
        $user->update(['last_login_at' => now()]);

        // Check if request is AJAX (for modal)
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Welcome to BookStore!',
                'redirect' => route('user.dashboard')
            ]);
        }

        return redirect()->route('user.dashboard')->with('success', 'Registration successful! Welcome to BookStore!');
    }
}
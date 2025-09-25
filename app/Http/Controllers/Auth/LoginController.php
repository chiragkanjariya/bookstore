<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Update last login time
            Auth::user()->update(['last_login_at' => now()]);

            // Check if request is AJAX (for modal)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful!',
                    'redirect' => $this->getRedirectPath(Auth::user())
                ]);
            }

            return redirect()->intended($this->getRedirectPath(Auth::user()));
        }

        // If login fails
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
                'errors' => [
                    'email' => ['The provided credentials do not match our records.']
                ]
            ], 422);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully!'
            ]);
        }

        return redirect('/');
    }

    /**
     * Get redirect path based on user role
     */
    private function getRedirectPath(User $user): string
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard');
        }
        
        return route('home');
    }
}
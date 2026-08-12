<?php

namespace App\Http\Middleware;

use App\Support\AdminMenu;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MenuPermissionMiddleware
{
    /**
     * Guard an admin route behind a menu permission.
     *
     * Runs after `auth` and `role:admin`, so it only has to answer whether this
     * admin's role grants the menu.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $menu): Response
    {
        $user = Auth::user();

        if (!$user || !$user->canAccessMenu($menu)) {
            $message = 'You do not have permission to access ' . AdminMenu::label($menu) . '.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 403);
            }

            return redirect()->route('admin.dashboard')->with('error', $message);
        }

        return $next($request);
    }
}

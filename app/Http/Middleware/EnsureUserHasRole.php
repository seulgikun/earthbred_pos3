<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        // Also fallback to session user_role if user object not populated in some custom session scenarios
        $userRole = $user ? $user->role : session('user_role');

        if (!$user && !$userRole) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please log in.'
                ], 401);
            }
            return redirect()->route('login');
        }

        if (!empty($roles) && !in_array($userRole, $roles)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You do not have permission to access this resource.'
                ], 403);
            }
            // For web pages, redirect unauthorized users based on their role
            if ($userRole === 'cashier') {
                return redirect('/pos');
            }
            return redirect('/manager');
        }

        return $next($request);
    }
}

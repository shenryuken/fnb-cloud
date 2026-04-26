<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Landlord users (tenant_id = null) have all permissions
        if (is_null($user->tenant_id)) {
            return $next($request);
        }

        // Check if user has the required permission
        if (!$user->hasPermission($permission)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}

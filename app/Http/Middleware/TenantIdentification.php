<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantIdentification
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->routeIs('landlord.*')
            || $request->is('landlord')
            || $request->is('landlord/*')
            || preg_match('~(^|/)landlord(/|$)~', $request->path())
        ) {
            return $next($request);
        }

        $tenantId = null;

        // Resolve the authenticated user. For API/native clients the user is
        // authenticated via a Sanctum bearer token, which the default (session)
        // guard won't see during this group-middleware pass — so consult the
        // sanctum guard explicitly as a fallback.
        $user = $request->user();
        if (!$user && $request->bearerToken()) {
            $user = auth('sanctum')->user();
            if ($user) {
                $request->setUserResolver(fn () => $user);
            }
        }

        // 1. Check if user is authenticated and has a tenant_id
        if ($user && $user->tenant_id) {
            $tenantId = $user->tenant_id;
        }

        // If a logged-in user has no tenant_id, treat them as a landlord/system user.
        // Landlord users must not access tenant-scoped pages (POS/KDS/Orders/Menu/etc) without impersonation.
        if (!$tenantId && $user) {
            if (
                $request->is('dashboard')
                || $request->is('settings')
                || $request->is('settings/*')
                || $request->is('user/confirm-password')
                || $request->is('user/confirmed-password-status')
                || $request->is('user/two-factor-authentication')
                || $request->is('user/confirmed-two-factor-authentication')
                || $request->is('user/two-factor-qr-code')
                || $request->is('user/two-factor-secret-key')
                || $request->is('user/two-factor-recovery-codes')
                || $request->is('two-factor-challenge')
                || $request->is('logout')
                || $request->is('livewire/*')
                || $request->is('up')
            ) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                abort(403, 'Access denied.');
            }

            return redirect()->route('landlord.dashboard');
        }

        // 2. Fallback to header (API)
        if (!$tenantId) {
            $tenantId = $request->header('X-Tenant-Id');
        }

        // 3. Fallback to subdomain identification
        if (!$tenantId) {
            $host = $request->getHost();
            $subdomain = explode('.', $host)[0];

            $tenant = Tenant::where('slug', $subdomain)->first();
            if ($tenant) {
                $tenantId = $tenant->id;
            }
        }

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);

            if ($tenant && ((!$tenant->is_active) || (($tenant->status ?? 'active') === 'suspended'))) {
                abort(403, 'This tenant is currently inactive.');
            }

            // Bind the tenant_id to the service container
            app()->instance('tenant_id', $tenantId);
        }

        return $next($request);
    }
}

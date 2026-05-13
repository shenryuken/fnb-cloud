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
        if ($request->is('landlord') || $request->is('landlord/*')) {
            return $next($request);
        }

        $tenantId = null;

        // 1. Check if user is authenticated and has a tenant_id
        if ($request->user() && $request->user()->tenant_id) {
            $tenantId = $request->user()->tenant_id;
        }

        // If a logged-in user has no tenant_id, treat them as a landlord/system user and avoid binding
        // a tenant based on host/header (prevents cross-scope issues on admin pages).
        if (!$tenantId && $request->user()) {
            return $next($request);
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

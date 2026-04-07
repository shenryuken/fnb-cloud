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
        $tenantId = null;

        // 1. Check if user is authenticated and has a tenant_id
        if ($request->user() && $request->user()->tenant_id) {
            $tenantId = $request->user()->tenant_id;
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
            // Bind the tenant_id to the service container
            app()->instance('tenant_id', $tenantId);
        }

        return $next($request);
    }
}

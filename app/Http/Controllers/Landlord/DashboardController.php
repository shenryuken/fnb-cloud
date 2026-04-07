<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'total_users' => User::whereNotNull('tenant_id')->count(),
            'total_orders' => Order::withoutGlobalScope(\App\Scopes\TenantScope::class)->count(),
            'total_revenue' => Order::withoutGlobalScope(\App\Scopes\TenantScope::class)->sum('total_amount'),
        ];

        $recentTenants = Tenant::latest()->take(5)->get();

        return view('landlord.dashboard', compact('stats', 'recentTenants'));
    }
}

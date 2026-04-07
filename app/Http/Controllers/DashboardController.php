<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the tenant-specific dashboard.
     */
    public function index(Request $request)
    {
        // If system admin, redirect to landlord dashboard
        if ($request->user()->tenant_id === null) {
            return redirect()->route('landlord.dashboard');
        }

        // TenantScope filters everything automatically
        $data = [
            'categoryCount' => Category::count(),
            'productCount' => Product::count(),
            'orderCount' => Order::count(),
            'totalRevenue' => Order::where('status', 'completed')->sum('total_amount'),
            'recentOrders' => Order::with('user')->latest()->take(5)->get(),
            'tenant' => $request->user()->tenant,
        ];

        return view('dashboard', $data);
    }
}

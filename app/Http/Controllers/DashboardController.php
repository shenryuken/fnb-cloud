<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $completedOrders = Order::where('status', 'completed');

        $data = [
            'categoryCount' => Category::count(),
            'productCount' => Product::count(),
            'orderCount' => Order::count(),
            'totalRevenue' => (clone $completedOrders)->sum('total_amount'),

            // Time-based revenue
            'todaySales'     => (clone $completedOrders)->whereDate('created_at', today())->sum('total_amount'),
            'todayOrders'    => (clone $completedOrders)->whereDate('created_at', today())->count(),
            'weekSales'      => (clone $completedOrders)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_amount'),
            'weekOrders'     => (clone $completedOrders)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'monthSales'     => (clone $completedOrders)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_amount'),
            'monthOrders'    => (clone $completedOrders)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),

            // Top selling products this month (by quantity sold)
            'topProducts' => OrderItem::with('product')
                ->whereHas('order', fn ($q) => $q->where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year))
                ->select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
                ->groupBy('product_id')
                ->orderByDesc('total_qty')
                ->take(5)
                ->get(),

            'recentOrders' => Order::with(['user', 'customer'])->latest()->take(5)->get(),
            'tenant' => $request->user()->tenant,
        ];

        return view('dashboard', $data);
    }
}

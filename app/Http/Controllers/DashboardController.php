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

            // Chart data for different time periods
            'weeklyChartData' => $this->getWeeklyChartData($completedOrders),
            'monthlyChartData' => $this->getMonthlyChartData($completedOrders),
            'yearlyChartData' => $this->getYearlyChartData($completedOrders),
        ];

        return view('dashboard', $data);
    }

    /**
     * Get daily sales data for the last 7 days
     */
    private function getWeeklyChartData($baseQuery): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $sales = (clone $baseQuery)
                ->whereDate('created_at', $date)
                ->sum('total_amount');
            $data[] = [
                'label' => $date->format('M d'),
                'value' => (float) $sales,
            ];
        }
        return $data;
    }

    /**
     * Get weekly sales data for the last 4 weeks
     */
    private function getMonthlyChartData($baseQuery): array
    {
        $data = [];
        $weeks = [];
        
        // Collect 4 weeks backwards
        for ($i = 3; $i >= 0; $i--) {
            $startOfWeek = now()->subWeeks($i)->startOfWeek();
            $endOfWeek = now()->subWeeks($i)->endOfWeek();
            $weeks[] = [
                'start' => $startOfWeek,
                'end' => $endOfWeek,
            ];
        }
        
        // Process weeks in order (oldest to newest)
        foreach ($weeks as $index => $week) {
            $sales = (clone $baseQuery)
                ->whereBetween('created_at', [$week['start'], $week['end']])
                ->sum('total_amount');
            $data[] = [
                'label' => 'Week ' . ($index + 1),
                'value' => (float) $sales,
            ];
        }
        return $data;
    }

    /**
     * Get monthly sales data for the last 12 months
     */
    private function getYearlyChartData($baseQuery): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $sales = (clone $baseQuery)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total_amount');
            $data[] = [
                'label' => $date->format('M'),
                'value' => (float) $sales,
            ];
        }
        return $data;
    }
}

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

        // Today's metrics
        $todaySales = (clone $completedOrders)->whereDate('created_at', today())->sum('total_amount');
        $todayOrders = (clone $completedOrders)->whereDate('created_at', today())->count();
        $todayAvgOrder = $todayOrders > 0 ? $todaySales / $todayOrders : 0;

        // Yesterday's metrics for comparison
        $yesterdaySales = (clone $completedOrders)->whereDate('created_at', today()->subDay())->sum('total_amount');
        $yesterdayOrders = (clone $completedOrders)->whereDate('created_at', today()->subDay())->count();

        // Calculate percentage changes
        $salesChange = $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : ($todaySales > 0 ? 100 : 0);
        $ordersChange = $yesterdayOrders > 0 ? (($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100 : ($todayOrders > 0 ? 100 : 0);

        // Kitchen/KDS status
        $pendingKds = Order::whereIn('kds_status', ['pending', 'preparing'])->count();
        $preparingOrders = Order::where('kds_status', 'preparing')->count();
        $readyOrders = Order::where('kds_status', 'ready')->count();

        // Week and month stats
        $weekSales = (clone $completedOrders)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_amount');
        $weekOrders = (clone $completedOrders)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $monthSales = (clone $completedOrders)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_amount');
        $monthOrders = (clone $completedOrders)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Last week comparison
        $lastWeekSales = (clone $completedOrders)->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->sum('total_amount');
        $weekChange = $lastWeekSales > 0 ? (($weekSales - $lastWeekSales) / $lastWeekSales) * 100 : ($weekSales > 0 ? 100 : 0);

        $data = [
            'categoryCount' => Category::count(),
            'productCount' => Product::count(),
            'orderCount' => Order::count(),
            'totalRevenue' => (clone $completedOrders)->sum('total_amount'),

            // Today metrics with comparisons
            'todaySales'     => $todaySales,
            'todayOrders'    => $todayOrders,
            'todayAvgOrder'  => $todayAvgOrder,
            'salesChange'    => $salesChange,
            'ordersChange'   => $ordersChange,

            // Week and month
            'weekSales'      => $weekSales,
            'weekOrders'     => $weekOrders,
            'weekChange'     => $weekChange,
            'monthSales'     => $monthSales,
            'monthOrders'    => $monthOrders,

            // KDS/Kitchen status
            'pendingKds'     => $pendingKds,
            'preparingOrders' => $preparingOrders,
            'readyOrders'    => $readyOrders,

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

            'recentOrders' => Order::with(['user', 'customer', 'items'])->latest()->take(5)->get(),
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
     * Get weekly sales data for all 52 weeks of the current year
     */
    private function getMonthlyChartData($baseQuery): array
    {
        $data = [];
        $year = now()->year;
        $startOfYear = now()->startOfYear();
        $currentWeek = now()->weekOfYear;

        for ($week = 1; $week <= 52; $week++) {
            // Calculate start and end of this ISO week in the current year
            $startOfWeek = (clone $startOfYear)->setISODate($year, $week)->startOfDay();
            $endOfWeek = (clone $startOfWeek)->addDays(6)->endOfDay();

            $sales = (clone $baseQuery)
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->sum('total_amount');

            $data[] = [
                'label' => 'W' . $week,
                'value' => (float) $sales,
                'isCurrent' => $week === $currentWeek,
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

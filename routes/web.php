<?php

use App\Livewire\Settings\Receipt as ReceiptSettings;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Landlord\DashboardController as LandlordDashboard;
use App\Livewire\Landlord\Tenants;
use App\Livewire\Menu\Categories;
use App\Livewire\Menu\Products;
use App\Livewire\Menu\Addons;
use App\Livewire\Orders;
use App\Livewire\Pos;
use App\Livewire\Kds;
use App\Livewire\SalesReport;
use App\Livewire\Customers;
use App\Livewire\Vouchers;
use App\Livewire\Shifts;
use App\Livewire\Tables;
use App\Livewire\CashierReport;
use App\Livewire\UserGuide;
use App\Livewire\Settings\Roles;
use App\Livewire\Settings\Users;
use App\Livewire\Settings\Loyalty as LoyaltySettings;
use App\Livewire\Settings\QuickNotes as QuickNotesSettings;
use App\Livewire\UnshiftedOrders;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Shared Dashboard (redirects based on role if needed, or handles both)
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Landlord Routes
    Route::middleware(['landlord'])->prefix('landlord')->name('landlord.')->group(function () {
        Route::get('dashboard', [LandlordDashboard::class, 'index'])->name('dashboard');
        Route::get('tenants', Tenants::class)->name('tenants.index');
    });
    
    // POS
    Route::get('pos', Pos::class)->name('pos.index')->middleware('permission:pos.access');
    Route::get('pos/receipt/{order}', function (\App\Models\Order $order) {
        // Ensure the order belongs to the current tenant (handled by TenantScope)
        return view('pos.receipt', [
            'order' => $order->load(['items.product', 'items.variant', 'items.addons', 'items.components', 'user']),
            'tenant' => Auth::user()->tenant,
            'issuedVouchers' => \App\Models\CustomerVoucher::query()
                ->where('issued_from_order_id', $order->id)
                ->orderBy('id')
                ->get(['code', 'expires_at']),
        ]);
    })->name('pos.receipt')->middleware('permission:pos.access');
    
    // Bill (for unpaid orders - customer requests bill before paying)
    Route::get('pos/bill/{order}', function (\App\Models\Order $order) {
        return view('pos.bill', [
            'order' => $order->load(['items.product', 'items.variant', 'items.addons', 'items.components', 'user']),
            'tenant' => Auth::user()->tenant,
        ]);
    })->name('pos.bill')->middleware('permission:pos.access');
    
    // Menu Management
    Route::get('categories', Categories::class)->name('manage.categories.index')->middleware('permission:menu.manage');
    Route::get('products', Products::class)->name('manage.products.index')->middleware('permission:menu.manage');
    Route::get('addons', Addons::class)->name('manage.addons.index')->middleware('permission:menu.manage');
    Route::get('customers', Customers::class)->name('manage.customers.index')->middleware('permission:customers.manage');
    Route::get('vouchers', Vouchers::class)->name('manage.vouchers.index')->middleware('permission:vouchers.manage');
    
    // Order Management
    Route::get('orders', Orders::class)->name('manage.orders.index')->middleware('permission:orders.manage');
    Route::get('orders/unshifted', UnshiftedOrders::class)->name('manage.orders.unshifted')->middleware('permission:orders.manage');
    Route::get('kds', Kds::class)->name('kds.index')->middleware('permission:kds.access');

    // Reports
    Route::get('reports/sales', SalesReport::class)->name('reports.sales')->middleware('permission:reports.view');
    Route::get('reports/cashier', CashierReport::class)->name('reports.cashier')->middleware('permission:reports.view');

    // Shifts
    Route::get('shifts', Shifts::class)->name('manage.shifts.index')->middleware('permission:pos.access');

    // Tables
    Route::get('tables', Tables::class)->name('manage.tables.index')->middleware('permission:pos.access');

    // User Guide
    Route::get('guide', UserGuide::class)->name('guide.index');

    // Tenant Settings (requires settings.manage permission)
    Route::get('settings/receipt', ReceiptSettings::class)->name('manage.settings.receipt')->middleware('permission:settings.manage');
    Route::get('settings/loyalty', LoyaltySettings::class)->name('manage.settings.loyalty')->middleware('permission:settings.manage');
    Route::get('settings/quick-notes', QuickNotesSettings::class)->name('manage.settings.quick_notes')->middleware('permission:settings.manage');
    Route::get('settings/roles', Roles::class)->name('manage.settings.roles')->middleware('permission:roles.manage');
    Route::get('settings/users', Users::class)->name('manage.settings.users')->middleware('permission:roles.manage');
});

require __DIR__.'/settings.php';

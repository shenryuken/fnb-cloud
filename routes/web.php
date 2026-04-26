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
use App\Livewire\Settings\Roles;
use App\Livewire\Settings\Loyalty as LoyaltySettings;
use App\Livewire\Settings\QuickNotes as QuickNotesSettings;
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
    Route::get('pos', Pos::class)->name('pos.index');
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
    })->name('pos.receipt');
    
    // Menu Management
    Route::get('categories', Categories::class)->name('manage.categories.index');
    Route::get('products', Products::class)->name('manage.products.index');
    Route::get('addons', Addons::class)->name('manage.addons.index');
    Route::get('customers', Customers::class)->name('manage.customers.index');
    Route::get('vouchers', Vouchers::class)->name('manage.vouchers.index');
    
    // Order Management
    Route::get('orders', Orders::class)->name('manage.orders.index');
    Route::get('kds', Kds::class)->name('kds.index');

    // Reports
    Route::get('reports/sales', SalesReport::class)->name('reports.sales');

    // Shifts
    Route::get('shifts', Shifts::class)->name('manage.shifts.index');

    // Tenant Settings
    Route::get('settings/receipt', ReceiptSettings::class)->name('manage.settings.receipt');
    Route::get('settings/loyalty', LoyaltySettings::class)->name('manage.settings.loyalty');
    Route::get('settings/quick-notes', QuickNotesSettings::class)->name('manage.settings.quick_notes');
    Route::get('settings/roles', Roles::class)->name('manage.settings.roles');
});

require __DIR__.'/settings.php';

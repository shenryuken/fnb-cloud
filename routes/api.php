<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\V1\MenuController as V1MenuController;
use App\Http\Controllers\Api\V1\OrderController as V1OrderController;
use App\Http\Controllers\Api\V1\SyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1
|--------------------------------------------------------------------------
| Versioned, Sanctum-authenticated API consumed by native/offline clients.
| Controllers stay thin and delegate to Actions/Services (single source of
| truth shared with the Livewire web app).
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public auth
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', fn (Request $request) => $request->user())->name('user');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Catalog (supports ?since= delta sync)
        Route::get('/menu', [V1MenuController::class, 'index'])->name('menu.index');

        // Orders
        Route::get('/orders', [V1OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [V1OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders', [V1OrderController::class, 'store'])->name('orders.store');

        // Offline sync
        Route::get('/sync/bootstrap', [SyncController::class, 'bootstrap'])->name('sync.bootstrap');
        Route::post('/sync/orders', [SyncController::class, 'syncOrders'])->name('sync.orders');
    });
});

/*
|--------------------------------------------------------------------------
| Legacy (unversioned) API — retained for backward compatibility
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/menu', [V1MenuController::class, 'index']);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('orders', V1OrderController::class)->only(['index', 'show', 'store']);
});

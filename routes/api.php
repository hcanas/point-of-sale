<?php

use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    // Members
    Route::resource('members', MemberController::class)->only(['index', 'store', 'update']);

    // Products & Inventory
    Route::resource('products', ProductController::class)->only(['index', 'store', 'update']);
    Route::post('products/{product}/stock-movements', [StockMovementController::class, 'store'])->name('api.products.stock-movements.store');

    // Users
    Route::resource('users', UserController::class)->only(['store', 'update']);

    // Transactions
    Route::get('sales', [SaleController::class, 'index'])->name('api.sales.index');
    Route::get('purchases', [PurchaseController::class, 'index'])->name('api.purchases.index');
    Route::get('payments', [PaymentController::class, 'index'])->name('api.payments.index');
});

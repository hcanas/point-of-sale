<?php

use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/members', [MemberController::class, 'store'])->name('api.members.store');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('api.members.update');
    Route::post('/products', [ProductController::class, 'store'])->name('api.products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('api.products.update');
    Route::post('/products/{product}/stock-movements', [StockMovementController::class, 'store'])->name('api.products.stock-movements.store');
    Route::post('/users', [UserController::class, 'store'])->name('api.users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('api.users.update');
});

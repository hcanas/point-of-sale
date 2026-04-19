<?php

use App\Http\Controllers\Api\MemberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/members', [MemberController::class, 'store'])->name('api.members.store');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('api.members.update');
});

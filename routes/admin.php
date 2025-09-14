<?php

use App\Http\Controllers\Admin\PayoutRequestsController;
use App\Http\Controllers\Admin\TipsController;
use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.users.index');
    })->name('admin.home');

    // Users
    Route::get('/users', [UsersController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{user}', [UsersController::class, 'show'])->name('admin.users.show');
    Route::patch('/users/{user}/profile', [UsersController::class, 'updateProfile'])->name('admin.users.profile.update');

    // Tips
    Route::get('/tips', [TipsController::class, 'index'])->name('admin.tips.index');

    // Payout Requests
    Route::get('/payout-requests', [PayoutRequestsController::class, 'index'])->name('admin.payout-requests.index');
    Route::patch('/payout-requests/{payoutRequest}', [PayoutRequestsController::class, 'update'])->name('admin.payout-requests.update');
});


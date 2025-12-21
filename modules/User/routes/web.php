<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\PasswordController;
use Modules\User\Http\Controllers\ProfileController;

Route::domain(config('admin.url'))->prefix(config('admin.prefix'))->group(function () {
    Route::middleware(['auth', 'permission:access-dashboard'])->group(function () {
        Route::redirect('settings', '/settings/profile');

        Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

        Route::put('settings/password', [PasswordController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('user-password.update');
    });
});

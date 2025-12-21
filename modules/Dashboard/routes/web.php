<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Modules\Dashboard\Http\Controllers\DashboardController;

Route::domain(config('admin.url'))->prefix(config('admin.prefix'))->group(function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified', 'permission:access-dashboard'])
        ->name('dashboard');

    Route::middleware(['auth', 'permission:access-dashboard'])->group(function () {
        Route::get('settings/appearance', function () {
            return Inertia::render('settings/Appearance');
        })->name('appearance.edit');
    });
});

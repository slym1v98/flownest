<?php

use Illuminate\Support\Facades\Route;
use Modules\Authentication\Http\Controllers\TwoFactorAuthenticationController;

Route::domain(config('admin.url'))->prefix(config('admin.prefix'))->group(function () {
    Route::middleware(['auth', 'permission:access-dashboard'])->group(function () {
        Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
            ->name('two-factor.show');
    });
});

<?php

use Illuminate\Support\Facades\Route;
// use Modules\Authorization\Http\Controllers\AuthorizationController;

Route::domain(config('admin.url'))->prefix(config('admin.prefix'))->group(function () {
    // Route::resource('authorizations', AuthorizationController::class)->names('authorization');
});

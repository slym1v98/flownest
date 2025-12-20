<?php

use Illuminate\Support\Facades\Route;
// use Modules\Authorization\Http\Controllers\AuthorizationController;

Route::domain(config('api.url'))->prefix(config('api.prefix'))->group(function () {
    // Route::apiResource('authorizations', AuthorizationController::class)->names('authorization');
});

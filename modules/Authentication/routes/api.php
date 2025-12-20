<?php

use Illuminate\Support\Facades\Route;
// use Modules\Authentication\Http\Controllers\AuthenticationController;

Route::domain(config('api.url'))->prefix(config('api.prefix'))->group(function () {
    // Route::apiResource('authentications', AuthenticationController::class)->names('authentication');
});

<?php

use Illuminate\Support\Facades\Route;
// use Modules\User\Http\Controllers\UserController;

Route::domain(config('api.url'))->prefix(config('api.prefix'))->group(function () {
    // Route::apiResource('users', UserController::class)->names('user');
});

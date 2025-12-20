<?php

use Illuminate\Support\Facades\Route;
// use Modules\Authentication\Http\Controllers\AuthenticationController;

Route::domain(config('admin.url'))->prefix(config('admin.prefix'))->group(function () {
    // Route::resource('authentications', AuthenticationController::class)->names('authentication');
});

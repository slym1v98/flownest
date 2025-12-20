<?php

use Illuminate\Support\Facades\Route;
// use Modules\User\Http\Controllers\UserController;

Route::domain(config('admin.url'))->prefix(config('admin.prefix'))->group(function () {
    // Route::resource('users', UserController::class)->names('user');
});

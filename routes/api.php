<?php

use Illuminate\Support\Facades\Route;

Route::domain(config('api.url'))->prefix(config('api.prefix'))->group(function () {
    Route::get('/', fn() => response()->json(['message' => 'Welcome to the API!']));
});

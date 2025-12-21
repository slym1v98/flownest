<?php

use Illuminate\Support\Facades\Route;

Route::domain(config('api.url'))->prefix(config('api.prefix'))->group(function () {
    // Define your public API routes here
});

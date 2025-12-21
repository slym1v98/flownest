<?php

use Illuminate\Support\Facades\Route;

Route::domain(config('admin.url'))->prefix(config('admin.prefix'))->group(function () {
    // Define your authorization routes here
});

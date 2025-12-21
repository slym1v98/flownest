<?php

use Illuminate\Support\Facades\Route;

Route::domain(config('app.url'))->group(function () {
    Route::get('/', function () {
        return view('frontend');
    })->name('home');
});

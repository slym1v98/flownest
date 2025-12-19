<?php

Route::group([
    'domain' => config('api.url'),
    'prefix' => config('api.prefix'),
], function () {
    Route::get('/', fn() => response()->json(['message' => 'Welcome to the API!']));
});

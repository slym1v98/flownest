<?php

use Illuminate\Support\Facades\Route;
use Modules\Content\Http\Controllers\ContentController;
use Modules\Content\Http\Controllers\ContentTypeController;

Route::domain(config('admin.url'))->prefix(config('admin.prefix'))->group(function () {
    Route::resource('content-types', ContentTypeController::class);
    Route::resource('content-types.contents', ContentController::class);
});

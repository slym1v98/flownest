<?php

use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Public\PostController as PublicPostController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public post routes
Route::prefix('posts')->name('posts.')->middleware('cacheResponse')->group(function () {
    Route::get('/', [PublicPostController::class, 'index'])->name('index');
    Route::get('/{slug}', [PublicPostController::class, 'show'])->name('show');
});

// Admin routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('posts', PostController::class);
    
    // Media management
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::post('media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    Route::get('media/list', [MediaController::class, 'list'])->name('media.list');
});

require __DIR__.'/settings.php';

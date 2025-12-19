<?php

use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\UserPermissionController;
use App\Http\Controllers\Admin\WorkflowController;
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

    // User permissions management
    Route::get('users/permissions', [UserPermissionController::class, 'index'])
        ->middleware('can:manage-roles')
        ->name('users.permissions');
    Route::post('users/{user}/assign-role', [UserPermissionController::class, 'assignRole'])
        ->middleware('can:manage-roles')
        ->name('users.assign-role');
    Route::post('users/{user}/remove-role', [UserPermissionController::class, 'removeRole'])
        ->middleware('can:manage-roles')
        ->name('users.remove-role');

    // Workflow management
    Route::post('posts/{post}/submit-for-review', [WorkflowController::class, 'submitForReview'])
        ->name('posts.submit-review');
    Route::post('posts/{post}/approve', [WorkflowController::class, 'approve'])
        ->middleware('can:publish-posts')
        ->name('posts.approve');
    Route::post('posts/{post}/reject', [WorkflowController::class, 'reject'])
        ->middleware('can:publish-posts')
        ->name('posts.reject');
    Route::post('posts/{post}/revisions/{revision}/restore', [WorkflowController::class, 'restore'])
        ->name('posts.revisions.restore');
});

require __DIR__.'/settings.php';

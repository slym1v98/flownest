<?php

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    // Public API endpoints for headless CMS
    Route::prefix('v1')->middleware('cacheResponse')->group(function () {
        // Posts endpoints
        Route::get('/posts', function (Request $request) {
            $query = Post::with(['user', 'media'])
                ->where('status', 'published')
                ->when($request->input('search'), function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('excerpt', 'like', "%{$search}%")
                            ->orWhere('content', 'like', "%{$search}%");
                    });
                })
                ->when($request->input('featured') === 'true', function ($query) {
                    $query->where('is_featured', true);
                })
                ->latest('created_at');

            $posts = $query->paginate($request->input('per_page', 15));

            return PostResource::collection($posts);
        });

        Route::get('/posts/{slug}', function (string $slug) {
            $post = Post::with(['user', 'media'])
                ->where('slug', $slug)
                ->where('status', 'published')
                ->firstOrFail();

            return new PostResource($post);
        });
    });
});

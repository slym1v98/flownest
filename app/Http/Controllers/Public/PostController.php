<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Display a listing of published posts.
     */
    public function index(Request $request): Response
    {
        $query = Post::with(['user', 'media'])
            ->where('status', 'published')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when($request->input('featured'), function ($query) {
                $query->where('is_featured', true);
            })
            ->latest('created_at');

        $posts = $query->paginate(12)->withQueryString();

        // Transform posts for public view
        $posts->getCollection()->transform(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'is_featured' => $post->is_featured,
                'created_at' => $post->created_at->format('F j, Y'),
                'author' => [
                    'name' => $post->user->name,
                ],
                'featured_image' => $post->getFirstMediaUrl('images', 'preview') ?: null,
                'thumbnail' => $post->getFirstMediaUrl('images', 'thumb') ?: null,
            ];
        });

        return Inertia::render('public/posts/Index', [
            'posts' => $posts,
            'filters' => [
                'search' => $request->input('search'),
                'featured' => $request->input('featured'),
            ],
        ]);
    }

    /**
     * Display the specified published post.
     */
    public function show(string $slug): Response
    {
        $post = Post::with(['user', 'media'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Prepare SEO data
        $seoData = $post->seo_data ?? [];
        $metaTitle = $seoData['meta_title'] ?? $post->title;
        $metaDescription = $seoData['meta_description'] ?? $post->excerpt;
        $ogImage = $seoData['og_image'] ?? $post->getFirstMediaUrl('images', 'preview');
        
        return Inertia::render('public/posts/Show', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'content' => $post->content,
                'excerpt' => $post->excerpt,
                'created_at' => $post->created_at->format('F j, Y'),
                'updated_at' => $post->updated_at->format('F j, Y'),
                'author' => [
                    'name' => $post->user->name,
                ],
                'featured_image' => $post->getFirstMediaUrl('images', 'preview') ?: null,
                'images' => $post->getMedia('images')->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'url' => $media->getUrl(),
                        'preview_url' => $media->getUrl('preview'),
                        'thumb_url' => $media->getUrl('thumb'),
                        'name' => $media->name,
                    ];
                }),
            ],
            'seo' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
                'keywords' => $seoData['meta_keywords'] ?? '',
                'og_title' => $seoData['og_title'] ?? $metaTitle,
                'og_description' => $seoData['og_description'] ?? $metaDescription,
                'og_image' => $ogImage,
                'twitter_title' => $seoData['twitter_title'] ?? $metaTitle,
                'twitter_description' => $seoData['twitter_description'] ?? $metaDescription,
                'twitter_image' => $seoData['twitter_image'] ?? $ogImage,
            ],
        ]);
    }
}

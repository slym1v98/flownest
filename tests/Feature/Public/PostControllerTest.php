<?php

namespace Tests\Feature\Public;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Storage::fake('public');
    }

    public function test_public_posts_index_page_can_be_rendered()
    {
        $response = $this->get('/posts');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('public/posts/Index'));
    }

    public function test_only_published_posts_are_listed()
    {
        // Create posts with different statuses
        $publishedPost = Post::factory()->create([
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $draftPost = Post::factory()->create([
            'status' => 'draft',
            'user_id' => $this->user->id,
        ]);

        $archivedPost = Post::factory()->create([
            'status' => 'archived',
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/posts');

        $response->assertStatus(200);

        // Dump response to see what's happening
        if ($response->status() !== 200) {
            dump($response->getContent());
        }

        $response->assertInertia(fn ($page) => $page->component('public/posts/Index')
            ->has('posts.data', 1)
            ->where('posts.data.0.id', $publishedPost->id)
        );
    }

    public function test_posts_can_be_searched()
    {
        $post1 = Post::factory()->create([
            'title' => 'Laravel Tutorial',
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $post2 = Post::factory()->create([
            'title' => 'Vue.js Guide',
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/posts?search=Laravel');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('public/posts/Index')
            ->has('posts.data', 1)
            ->where('posts.data.0.id', $post1->id)
        );
    }

    public function test_featured_posts_can_be_filtered()
    {
        $featuredPost = Post::factory()->create([
            'is_featured' => true,
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $normalPost = Post::factory()->create([
            'is_featured' => false,
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/posts?featured=true');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('public/posts/Index')
            ->has('posts.data', 1)
            ->where('posts.data.0.id', $featuredPost->id)
        );
    }

    public function test_published_post_can_be_viewed()
    {
        $post = Post::factory()->create([
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $response = $this->get("/posts/{$post->slug}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('public/posts/Show')
            ->where('post.id', $post->id)
            ->where('post.title', $post->title)
            ->has('seo')
        );
    }

    public function test_draft_post_cannot_be_viewed()
    {
        $post = Post::factory()->create([
            'status' => 'draft',
            'user_id' => $this->user->id,
        ]);

        $response = $this->get("/posts/{$post->slug}");

        $response->assertStatus(404);
    }

    public function test_archived_post_cannot_be_viewed()
    {
        $post = Post::factory()->create([
            'status' => 'archived',
            'user_id' => $this->user->id,
        ]);

        $response = $this->get("/posts/{$post->slug}");

        $response->assertStatus(404);
    }

    public function test_post_view_includes_seo_metadata()
    {
        $post = Post::factory()->create([
            'status' => 'published',
            'user_id' => $this->user->id,
            'seo_data' => [
                'meta_title' => 'Custom SEO Title',
                'meta_description' => 'Custom SEO Description',
                'meta_keywords' => 'laravel, php, testing',
            ],
        ]);

        $response = $this->get("/posts/{$post->slug}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('public/posts/Show')
            ->where('seo.title', 'Custom SEO Title')
            ->where('seo.description', 'Custom SEO Description')
            ->where('seo.keywords', 'laravel, php, testing')
        );
    }

    public function test_post_view_uses_default_seo_values_when_not_provided()
    {
        $post = Post::factory()->create([
            'title' => 'My Great Post',
            'excerpt' => 'This is an excerpt',
            'status' => 'published',
            'user_id' => $this->user->id,
            'seo_data' => null,
        ]);

        $response = $this->get("/posts/{$post->slug}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('public/posts/Show')
            ->where('seo.title', 'My Great Post')
            ->where('seo.description', 'This is an excerpt')
        );
    }

    public function test_post_list_includes_author_information()
    {
        $post = Post::factory()->create([
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/posts');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('public/posts/Index')
            ->has('posts.data.0.author')
            ->where('posts.data.0.author.name', $this->user->name)
        );
    }

    public function test_post_pagination_works()
    {
        // Create more than 12 posts (default pagination limit)
        Post::factory()->count(15)->create([
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/posts');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('public/posts/Index')
            ->has('posts.data', 12)
            ->where('posts.total', 15)
            ->where('posts.last_page', 2)
        );
    }
}

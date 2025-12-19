<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_api_returns_published_posts()
    {
        $publishedPost = Post::factory()->create([
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $draftPost = Post::factory()->create([
            'status' => 'draft',
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'content',
                        'excerpt',
                        'status',
                        'is_featured',
                        'created_at',
                        'updated_at',
                        'author',
                        'featured_image',
                        'thumbnail',
                        'images',
                        'seo',
                    ],
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $publishedPost->id);
    }

    public function test_api_post_by_slug_returns_correct_post()
    {
        $post = Post::factory()->create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/posts/test-post');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.title', 'Test Post')
            ->assertJsonPath('data.slug', 'test-post');
    }

    public function test_api_returns_404_for_draft_post()
    {
        $post = Post::factory()->create([
            'status' => 'draft',
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/posts/{$post->slug}");

        $response->assertStatus(404);
    }

    public function test_api_returns_404_for_archived_post()
    {
        $post = Post::factory()->create([
            'status' => 'archived',
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/posts/{$post->slug}");

        $response->assertStatus(404);
    }

    public function test_api_returns_404_for_nonexistent_post()
    {
        $response = $this->getJson('/api/v1/posts/nonexistent-slug');

        $response->assertStatus(404);
    }

    public function test_api_includes_author_information()
    {
        $post = Post::factory()->create([
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/posts/{$post->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.author.id', $this->user->id)
            ->assertJsonPath('data.author.name', $this->user->name)
            ->assertJsonPath('data.author.email', $this->user->email);
    }

    public function test_api_includes_seo_data()
    {
        $post = Post::factory()->create([
            'status' => 'published',
            'user_id' => $this->user->id,
            'seo_data' => [
                'meta_title' => 'Custom SEO Title',
                'meta_description' => 'Custom SEO Description',
                'meta_keywords' => 'laravel, api, testing',
            ],
        ]);

        $response = $this->getJson("/api/v1/posts/{$post->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.seo.meta_title', 'Custom SEO Title')
            ->assertJsonPath('data.seo.meta_description', 'Custom SEO Description')
            ->assertJsonPath('data.seo.meta_keywords', 'laravel, api, testing');
    }

    public function test_api_search_filters_posts()
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

        $response = $this->getJson('/api/v1/posts?search=Laravel');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $post1->id);
    }

    public function test_api_featured_filter_works()
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

        $response = $this->getJson('/api/v1/posts?featured=true');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $featuredPost->id);
    }

    public function test_api_pagination_works()
    {
        Post::factory()->count(20)->create([
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/posts');

        $response->assertStatus(200)
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.per_page', 15);
    }

    public function test_api_custom_per_page_works()
    {
        Post::factory()->count(10)->create([
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/posts?per_page=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5);
    }

    public function test_api_returns_correct_timestamps_format()
    {
        $post = Post::factory()->create([
            'status' => 'published',
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/posts/{$post->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'created_at',
                    'updated_at',
                ],
            ]);

        // Verify ISO 8601 format
        $createdAt = $response->json('data.created_at');
        $this->assertNotNull($createdAt);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $createdAt);
    }
}

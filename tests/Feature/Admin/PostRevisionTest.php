<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\PostRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostRevisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_revision_is_created_on_post_creation(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Editor');

        // Create a post manually to test the revision creation
        $post = Post::create([
            'title' => ['en' => 'Test Post'],
            'slug' => 'test-post-rev',
            'content' => ['type' => 'doc', 'content' => []],
            'excerpt' => ['en' => 'Test excerpt'],
            'status' => 'draft',
            'is_featured' => false,
            'user_id' => $user->id,
        ]);

        // Create initial revision
        $post->createRevision('Initial version');

        $this->assertEquals(1, $post->revisions()->count());
        $this->assertEquals('Initial version', $post->revisions()->first()->reason);
    }

    public function test_revision_is_created_on_post_update(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Editor');

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => ['en' => 'Original Title'],
            'status' => 'draft',
        ]);

        // Clear any existing revisions
        $post->revisions()->delete();

        $response = $this->actingAs($user)->put(route('admin.posts.update', $post), [
            'title' => 'Updated Title',
            'slug' => $post->slug,
            'content' => $post->content,
            'excerpt' => $post->excerpt,
            'status' => 'draft',
            'is_featured' => false,
        ]);

        $this->assertGreaterThan(0, $post->revisions()->count());
    }

    public function test_revision_stores_post_data_correctly(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => ['en' => 'Test Title'],
            'slug' => 'test-slug',
            'content' => ['type' => 'doc'],
            'excerpt' => ['en' => 'Test excerpt'],
            'status' => 'draft',
            'is_featured' => true,
        ]);

        $revision = $post->createRevision('Manual revision');

        $this->assertInstanceOf(PostRevision::class, $revision);
        $this->assertEquals($post->id, $revision->post_id);
        $this->assertEquals($post->user_id, $revision->user_id);
        $this->assertEquals($post->title, $revision->title);
        $this->assertEquals($post->slug, $revision->slug);
        $this->assertEquals($post->content, $revision->content);
        $this->assertEquals($post->excerpt, $revision->excerpt);
        $this->assertEquals($post->status, $revision->status);
        $this->assertEquals($post->is_featured, $revision->is_featured);
        $this->assertEquals('Manual revision', $revision->reason);
    }

    public function test_user_can_restore_post_from_revision(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Editor');

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => ['en' => 'Current Title'],
            'slug' => 'current-slug',
        ]);

        // Create a revision with different data
        $revision = $post->revisions()->create([
            'user_id' => $user->id,
            'title' => ['en' => 'Old Title'],
            'slug' => 'old-slug',
            'content' => ['type' => 'doc'],
            'excerpt' => ['en' => 'Old excerpt'],
            'status' => 'draft',
            'is_featured' => false,
            'seo_data' => null,
            'reason' => 'Test revision',
        ]);

        $response = $this->actingAs($user)->post(
            route('admin.posts.revisions.restore', [$post, $revision->id])
        );

        $response->assertRedirect();
        $post = $post->fresh();
        // Use getTranslations to get the raw array
        $this->assertEquals(['en' => 'Old Title'], $post->getTranslations('title'));
        $this->assertEquals('old-slug', $post->slug);
    }

    public function test_revisions_are_ordered_by_latest(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $revision1 = $post->createRevision('First');
        sleep(1); // Ensure different timestamps
        $revision2 = $post->createRevision('Second');

        $revisions = $post->revisions;
        $this->assertEquals($revision2->id, $revisions->first()->id);
        $this->assertEquals($revision1->id, $revisions->last()->id);
    }

    public function test_multiple_revisions_can_be_created(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $post->createRevision('Revision 1');
        $post->createRevision('Revision 2');
        $post->createRevision('Revision 3');

        $this->assertEquals(3, $post->revisions()->count());
    }
}

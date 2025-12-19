<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;
use App\Notifications\PostPendingReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_editor_can_submit_post_for_review(): void
    {
        Notification::fake();

        $editor = User::factory()->create();
        $editor->assignRole('Editor');

        $publisher = User::factory()->create();
        $publisher->assignRole('Publisher');

        $post = Post::factory()->create([
            'user_id' => $editor->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($editor)->post(route('admin.posts.submit-review', $post));

        $response->assertRedirect();
        $this->assertEquals('pending_review', $post->fresh()->status);

        // Assert notification was sent to publishers
        Notification::assertSentTo($publisher, PostPendingReview::class);
    }

    public function test_publisher_can_approve_post(): void
    {
        $publisher = User::factory()->create();
        $publisher->assignRole('Publisher');

        $post = Post::factory()->create([
            'status' => 'pending_review',
        ]);

        $response = $this->actingAs($publisher)->post(
            route('admin.posts.approve', $post),
            ['review_notes' => 'Looks good!']
        );

        $response->assertRedirect();
        $post = $post->fresh();
        $this->assertEquals('published', $post->status);
        $this->assertEquals($publisher->id, $post->reviewed_by);
        $this->assertEquals('Looks good!', $post->review_notes);
        $this->assertNotNull($post->reviewed_at);
    }

    public function test_publisher_can_reject_post(): void
    {
        $publisher = User::factory()->create();
        $publisher->assignRole('Publisher');

        $post = Post::factory()->create([
            'status' => 'pending_review',
        ]);

        $response = $this->actingAs($publisher)->post(
            route('admin.posts.reject', $post),
            ['review_notes' => 'Needs more work']
        );

        $response->assertRedirect();
        $post = $post->fresh();
        $this->assertEquals('draft', $post->status);
        $this->assertEquals($publisher->id, $post->reviewed_by);
        $this->assertEquals('Needs more work', $post->review_notes);
    }

    public function test_editor_cannot_approve_post(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('Editor');

        $post = Post::factory()->create([
            'status' => 'pending_review',
        ]);

        $response = $this->actingAs($editor)->post(route('admin.posts.approve', $post));

        $response->assertStatus(403);
    }

    public function test_revision_is_created_on_workflow_actions(): void
    {
        $publisher = User::factory()->create();
        $publisher->assignRole('Publisher');

        $post = Post::factory()->create([
            'status' => 'pending_review',
        ]);

        $initialRevisionCount = $post->revisions()->count();

        $this->actingAs($publisher)->post(
            route('admin.posts.approve', $post),
            ['review_notes' => 'Approved']
        );

        $this->assertEquals($initialRevisionCount + 1, $post->revisions()->count());
    }
}

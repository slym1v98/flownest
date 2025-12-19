<?php

namespace Tests\Feature\Admin;

use App\Models\MediaItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class MediaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Storage::fake('public');
    }

    public function test_media_index_page_can_be_rendered()
    {
        $response = $this->actingAs($this->user)->get('/admin/media');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('admin/media/Index'));
    }

    public function test_media_can_be_uploaded()
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $response = $this->actingAs($this->user)
            ->post('/admin/media', [
                'files' => [$file],
                'collection' => 'default',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Files uploaded successfully',
        ]);

        $this->assertDatabaseHas('media_items', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_media_upload_validates_file_type()
    {
        $file = UploadedFile::fake()->create('test.txt', 100);

        $response = $this->actingAs($this->user)
            ->post('/admin/media', [
                'files' => [$file],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }

    public function test_user_can_only_delete_own_media()
    {
        // Create media for first user
        $mediaItem = MediaItem::create([
            'name' => 'Test Image',
            'user_id' => $this->user->id,
        ]);

        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaItem->addMedia($file)->toMediaCollection('default');

        // Try to delete as another user
        $anotherUser = User::factory()->create();

        $response = $this->actingAs($anotherUser)
            ->delete("/admin/media/{$media->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_user_can_delete_own_media()
    {
        $mediaItem = MediaItem::create([
            'name' => 'Test Image',
            'user_id' => $this->user->id,
        ]);

        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaItem->addMedia($file)->toMediaCollection('default');

        $response = $this->actingAs($this->user)
            ->delete("/admin/media/{$media->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Media deleted successfully',
        ]);
    }

    public function test_media_list_api_returns_json()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/media/list');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'media',
        ]);
    }
}

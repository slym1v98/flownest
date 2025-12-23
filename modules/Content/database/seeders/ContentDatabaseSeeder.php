<?php

namespace Modules\Content\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Content\Models\Content;
use Modules\Content\Models\ContentField;
use Modules\Content\Models\ContentType;

class ContentDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->runOnDevelopment();
        $this->runOnProduction();
    }

    protected function runOnDevelopment(): void
    {
        if (!app()->environment('local')) {
            return;
        }

        $post = ContentType::factory()->create([
            'name'        => 'Post',
            'slug'        => 'posts',
            'description' => 'Blog post',
            'editor'      => 'editor',
            'is_system'   => true
        ]);
        $page = ContentType::factory()->create([
            'name'        => 'Page',
            'slug'        => 'pages',
            'description' => 'Static page',
            'editor'      => 'gutenberg',
            'is_system'   => true
        ]);

        ContentField::factory()->create([
            'content_type_id' => $post->id,
            'label'           => 'Post thumbnail',
            'key'             => 'thumbnail',
            'type'            => 'image',
        ]);
        ContentField::factory()->createMany([
            [
                'content_type_id' => $page->id,
                'label'           => 'Page banner',
                'key'             => 'banner',
                'type'            => 'image',
                'order'           => 0,
            ], [
                'content_type_id' => $page->id,
                'label'           => 'Page template',
                'key'             => 'template',
                'type'            => 'text',
                'order'           => 1,
            ]
        ]);

        Content::factory(30)->create([
            'content_type_id' => $post->id,
            'attributes'      => [
                'thumbnail' => 'https://picsum.photos/400/300'
            ]
        ]);
        Content::factory(5)->create([
            'content_type_id' => $page->id,
            'attributes'      => [
                'banner'   => 'https://picsum.photos/1920/400',
                'template' => 'default'
            ]
        ]);
    }

    public function runOnProduction(): void
    {
        if (!app()->environment('production')) {
            return;
        }
    }
}

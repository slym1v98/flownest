<?php

namespace Tests\Feature\Admin;

use App\Models\ContentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_type_can_be_created_with_valid_schema()
    {
        $schema = [
            [
                'name' => 'title',
                'label' => 'Title',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'price',
                'label' => 'Price',
                'type' => 'number',
                'required' => false,
            ],
        ];

        $contentType = ContentType::create([
            'name' => 'Test Type',
            'slug' => 'test-type',
            'schema' => $schema,
        ]);

        $this->assertDatabaseHas('content_types', [
            'name' => 'Test Type',
            'slug' => 'test-type',
        ]);

        $this->assertEquals($schema, $contentType->schema);
    }

    public function test_schema_validates_correctly()
    {
        $validSchema = [
            [
                'name' => 'title',
                'label' => 'Title',
                'type' => 'text',
            ],
        ];

        $this->assertTrue(ContentType::validateSchema($validSchema));
    }

    public function test_schema_validation_fails_for_missing_required_fields()
    {
        $invalidSchema = [
            [
                'label' => 'Title',
                'type' => 'text',
                // Missing 'name'
            ],
        ];

        $this->assertFalse(ContentType::validateSchema($invalidSchema));
    }

    public function test_schema_validation_fails_for_invalid_field_type()
    {
        $invalidSchema = [
            [
                'name' => 'title',
                'label' => 'Title',
                'type' => 'invalid_type',
            ],
        ];

        $this->assertFalse(ContentType::validateSchema($invalidSchema));
    }

    public function test_content_type_seeder_creates_sample_types()
    {
        $this->seed(\Database\Seeders\ContentTypeSeeder::class);

        $this->assertDatabaseHas('content_types', [
            'slug' => 'projects',
        ]);

        $this->assertDatabaseHas('content_types', [
            'slug' => 'services',
        ]);

        $this->assertDatabaseHas('content_types', [
            'slug' => 'testimonials',
        ]);

        $this->assertDatabaseHas('content_types', [
            'slug' => 'team-members',
        ]);
    }
}

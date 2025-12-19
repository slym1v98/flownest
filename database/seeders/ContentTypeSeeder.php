<?php

namespace Database\Seeders;

use App\Models\ContentType;
use Illuminate\Database\Seeder;

class ContentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contentTypes = [
            [
                'name' => 'Projects',
                'slug' => 'projects',
                'icon' => 'briefcase',
                'description' => 'Portfolio projects and case studies',
                'schema' => [
                    [
                        'name' => 'title',
                        'label' => 'Project Title',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Enter project title',
                    ],
                    [
                        'name' => 'client',
                        'label' => 'Client Name',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'Client or company name',
                    ],
                    [
                        'name' => 'project_url',
                        'label' => 'Project URL',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'https://example.com',
                    ],
                    [
                        'name' => 'description',
                        'label' => 'Description',
                        'type' => 'rich_text',
                        'required' => true,
                        'placeholder' => 'Describe the project...',
                    ],
                    [
                        'name' => 'featured_image',
                        'label' => 'Featured Image',
                        'type' => 'image',
                        'required' => true,
                        'help_text' => 'Main project image',
                    ],
                    [
                        'name' => 'completion_date',
                        'label' => 'Completion Date',
                        'type' => 'date',
                        'required' => false,
                    ],
                    [
                        'name' => 'is_featured',
                        'label' => 'Featured Project',
                        'type' => 'boolean',
                        'required' => false,
                        'help_text' => 'Display on homepage',
                    ],
                ],
            ],
            [
                'name' => 'Services',
                'slug' => 'services',
                'icon' => 'wrench',
                'description' => 'Services offered by the company',
                'schema' => [
                    [
                        'name' => 'title',
                        'label' => 'Service Title',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Service name',
                    ],
                    [
                        'name' => 'description',
                        'label' => 'Description',
                        'type' => 'rich_text',
                        'required' => true,
                        'placeholder' => 'Describe the service...',
                    ],
                    [
                        'name' => 'icon',
                        'label' => 'Icon',
                        'type' => 'image',
                        'required' => false,
                        'help_text' => 'Service icon or image',
                    ],
                    [
                        'name' => 'price',
                        'label' => 'Starting Price',
                        'type' => 'number',
                        'required' => false,
                        'placeholder' => '99.99',
                        'help_text' => 'Starting price for this service',
                    ],
                    [
                        'name' => 'duration',
                        'label' => 'Typical Duration',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'e.g., 2-4 weeks',
                    ],
                    [
                        'name' => 'is_active',
                        'label' => 'Active Service',
                        'type' => 'boolean',
                        'required' => false,
                        'default_value' => true,
                    ],
                ],
            ],
            [
                'name' => 'Testimonials',
                'slug' => 'testimonials',
                'icon' => 'quote',
                'description' => 'Client testimonials and reviews',
                'schema' => [
                    [
                        'name' => 'client_name',
                        'label' => 'Client Name',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'John Doe',
                    ],
                    [
                        'name' => 'client_company',
                        'label' => 'Company',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'Company name',
                    ],
                    [
                        'name' => 'client_position',
                        'label' => 'Position',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'CEO, Manager, etc.',
                    ],
                    [
                        'name' => 'testimonial',
                        'label' => 'Testimonial',
                        'type' => 'textarea',
                        'required' => true,
                        'placeholder' => 'What did the client say?',
                    ],
                    [
                        'name' => 'rating',
                        'label' => 'Rating',
                        'type' => 'select',
                        'required' => true,
                        'options' => [
                            ['label' => '5 Stars', 'value' => 5],
                            ['label' => '4 Stars', 'value' => 4],
                            ['label' => '3 Stars', 'value' => 3],
                            ['label' => '2 Stars', 'value' => 2],
                            ['label' => '1 Star', 'value' => 1],
                        ],
                        'default_value' => 5,
                    ],
                    [
                        'name' => 'client_photo',
                        'label' => 'Client Photo',
                        'type' => 'image',
                        'required' => false,
                        'help_text' => 'Optional client headshot',
                    ],
                    [
                        'name' => 'date',
                        'label' => 'Date',
                        'type' => 'date',
                        'required' => false,
                    ],
                    [
                        'name' => 'is_featured',
                        'label' => 'Featured',
                        'type' => 'boolean',
                        'required' => false,
                        'help_text' => 'Show on homepage',
                    ],
                ],
            ],
            [
                'name' => 'Team Members',
                'slug' => 'team-members',
                'icon' => 'users',
                'description' => 'Team members and staff profiles',
                'schema' => [
                    [
                        'name' => 'full_name',
                        'label' => 'Full Name',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'John Doe',
                    ],
                    [
                        'name' => 'position',
                        'label' => 'Position',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Senior Developer',
                    ],
                    [
                        'name' => 'bio',
                        'label' => 'Biography',
                        'type' => 'textarea',
                        'required' => false,
                        'placeholder' => 'Short bio about the team member',
                    ],
                    [
                        'name' => 'photo',
                        'label' => 'Photo',
                        'type' => 'image',
                        'required' => true,
                        'help_text' => 'Professional headshot',
                    ],
                    [
                        'name' => 'email',
                        'label' => 'Email',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'john@example.com',
                    ],
                    [
                        'name' => 'linkedin_url',
                        'label' => 'LinkedIn URL',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'https://linkedin.com/in/johndoe',
                    ],
                    [
                        'name' => 'twitter_url',
                        'label' => 'Twitter URL',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'https://twitter.com/johndoe',
                    ],
                    [
                        'name' => 'order',
                        'label' => 'Display Order',
                        'type' => 'number',
                        'required' => false,
                        'help_text' => 'Order in team listing (lower numbers first)',
                        'default_value' => 0,
                    ],
                ],
            ],
        ];

        foreach ($contentTypes as $type) {
            ContentType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}

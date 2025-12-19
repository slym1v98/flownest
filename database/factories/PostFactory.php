<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'content' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => fake()->paragraphs(3, true),
                            ],
                        ],
                    ],
                ],
            ],
            'excerpt' => fake()->paragraph(),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'is_featured' => fake()->boolean(20),
            'seo_data' => [
                'meta_title' => $title,
                'meta_description' => fake()->sentence(),
                'meta_keywords' => implode(', ', fake()->words(5)),
            ],
            'user_id' => \App\Models\User::factory(),
        ];
    }
}

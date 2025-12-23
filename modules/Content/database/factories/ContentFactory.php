<?php

namespace Modules\Content\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Models\Content;

class ContentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Content::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title'       => fake()->sentence(),
            'slug'        => fake()->slug(),
            'excerpt'     => fake()->sentence('50'),
            'content'     => fake()->paragraphs(5, true),
            'is_featured' => fake()->boolean(),
            'attributes'  => [],
        ];
    }
}


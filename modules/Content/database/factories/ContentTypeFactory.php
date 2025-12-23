<?php

namespace Modules\Content\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Models\ContentType;

class ContentTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ContentType::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name'        => fake()->word(),
            'slug'        => fake()->slug(),
            'description' => fake()->sentence(),
            'editor'      => fake()->randomElement(['editor', 'gutenberg']),
            'is_system'   => false,
        ];
    }
}


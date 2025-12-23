<?php

namespace Modules\Content\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Models\ContentField;

class ContentFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ContentField::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'label' => fake()->word(),
            'key'   => fake()->unique()->userName(),
            'type'  => fake()->randomElement(['text', 'textarea', 'image', 'file']),
        ];
    }
}


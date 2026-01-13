<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement([Post::TYPE_VIDEO, Post::TYPE_NEWS]),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
        ];
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Post::TYPE_VIDEO,
        ]);
    }

    public function news(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Post::TYPE_NEWS,
        ]);
    }
}

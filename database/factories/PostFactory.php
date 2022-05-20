<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'category_id' => Category::factory(),
            'content' => $this->faker->paragraph,
            'posted_at' => now(),
            'author_id' => User::factory()
        ];
    }
}

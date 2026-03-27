<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'author_name' => fake()->userName(),
            'content' => fake()->paragraph(),
            'is_read' => false,
        ];
    }

    public function read(): static
    {
        return $this->state(['is_read' => true]);
    }
}

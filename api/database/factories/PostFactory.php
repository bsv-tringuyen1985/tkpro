<?php

namespace Database\Factories;

use App\Helpers\SlugHelper;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(6);

        return [
            'category_id' => PostCategory::factory(),
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => SlugHelper::unique(Post::class, SlugHelper::make($title)),
            'excerpt' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'status' => 'published',
            'published_at' => now(),
        ];
    }
}

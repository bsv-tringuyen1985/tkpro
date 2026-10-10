<?php

namespace Database\Factories;

use App\Helpers\SlugHelper;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostCategoryFactory extends Factory
{
    protected $model = PostCategory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => SlugHelper::unique(PostCategory::class, SlugHelper::make($name)),
            'description' => $this->faker->sentence(),
            'status' => 'published',
            'sort_order' => 0,
        ];
    }
}

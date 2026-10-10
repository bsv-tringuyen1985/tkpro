<?php

namespace Database\Factories;

use App\Helpers\SlugHelper;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => SlugHelper::unique(Page::class, SlugHelper::make($title)),
            'excerpt' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(2, true),
            'status' => 'published',
            'sort_order' => 0,
        ];
    }
}

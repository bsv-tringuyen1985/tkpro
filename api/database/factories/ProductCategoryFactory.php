<?php

namespace Database\Factories;

use App\Helpers\SlugHelper;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => SlugHelper::unique(ProductCategory::class, SlugHelper::make($name)),
            'description' => $this->faker->sentence(),
            'status' => 'published',
            'sort_order' => 0,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Helpers\SlugHelper;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(4, true);

        return [
            'category_id' => ProductCategory::factory(),
            'name' => $name,
            'slug' => SlugHelper::unique(Product::class, SlugHelper::make($name)),
            'sku' => strtoupper($this->faker->unique()->bothify('??-#####')),
            'short_description' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(500000, 10000000),
            'status' => 'published',
            'featured' => false,
            'sort_order' => 0,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $purchasePrice = fake()->randomFloat(2, 10, 1000);

        return [
            'name' => fake()->words(3, true),
            'sku' => 'SKU-'.Str::upper(Str::random(8)),
            'description' => fake()->optional()->sentence(),
            'selling_price' => $purchasePrice + fake()->randomFloat(2, 5, 250),
            'purchase_price' => $purchasePrice,
            'stock_quantity' => fake()->numberBetween(10, 250),
            'status' => fake()->randomElement(Product::STATUSES),
        ];
    }
}

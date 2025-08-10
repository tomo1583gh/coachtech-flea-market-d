<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(100, 10000),
            'user_id' => User::factory(),
            'is_sold' => false,
            'image_path' => 'default.jpg',
            'buyer_id' => null,
            'zip' => null,
            'address' => null,
            'building' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\TradeMessage;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class TradeMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $modal = TradeMessage::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'user_id'    => User::factory(),
            'body'       => $this->faker->sentence,
        ];
    }
}

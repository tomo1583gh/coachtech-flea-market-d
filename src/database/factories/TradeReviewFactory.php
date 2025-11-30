<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\TradeReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TradeReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'product_id'  => Product::factory(),   // 関連商品
            'reviewer_id' => User::factory(),      // 評価する側
            'reviewee_id' => User::factory(),      // 評価される側
            'rating'      => $this->faker->numberBetween(1, 5),
        ];
    }
}

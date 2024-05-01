<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'=>fake()->name,
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Product $product) {
            if(!empty($product->business_id)){
                return;
            }

            $business = Business::inRandomOrder()->limit(1)->first();

            if (empty($business)) {
                $business = Business::factory()->create();
            }

            $product->business_id = $business->id;
        });

    }
}

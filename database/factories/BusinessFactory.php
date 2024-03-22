<?php

namespace Database\Factories;

use Illuminate\Support\Facades\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
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
            'expiration_date'=>Carbon::now()->modify("+1 year"),
            'active'=>true
        ];
    }

    public function inactive()
    {
        return $this->afterMaking(function (Business $business){
           $business->active = false;
        });
    }

    public function expired()
    {
        return $this->afterMaking(function (Business $business){
            $business->expiration_date = (new Carbon())->modify("-2 days");
        });
    }
}

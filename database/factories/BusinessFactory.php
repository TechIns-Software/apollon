<?php

namespace Database\Factories;

use Carbon\Carbon;
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
            'is_active'=>true,
            'doy'=> "ΑΘΗΝΩΝ",
            'vat'=>mt_rand(100000000, 999999999) //ΑΦΜ
        ];
    }

    public function inactive()
    {
        return $this->afterMaking(function (Business $business){
           $business->is_active = false;
        });
    }

    public function expired()
    {
        return $this->afterMaking(function (Business $business){
            $business->expiration_date = (new Carbon())->modify("-2 days");
        });
    }
}

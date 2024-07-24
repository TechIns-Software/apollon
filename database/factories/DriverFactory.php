<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Driver;
use App\Models\SaasUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'driver_name'=>$this->faker->name(),
        ];
    }

    public function configure(){
        return $this->afterMaking(function (Driver $driver){
            if(!empty($driver->business_id)){
                return;
            }

            $business = Business::inRandomOrder()->first();
            if(empty($business)){
                $business = Business::factory()->create();
            }

            $driver->business_id = $business->id;
        });
    }

    public function withUser(SaasUser $user)
    {
        return $this->afterMaking(function (Driver $driver) use ($user){
            $driver->business_id = $user->business_id;
        });
    }
}

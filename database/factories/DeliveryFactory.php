<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Delivery;
use App\Models\DeliveryOrder;
use App\Models\Driver;
use App\Models\Order;
use App\Models\SaasUser;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Delivery>
 */
class DeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
        ];
    }

    public function businessFromUser(SaasUser $user)
    {
        return $this->afterMaking(function (Delivery $delivery) use ($user){
            $delivery->business_id=$user->business_id;
        });
    }

    public function withNewDriver()
    {
        return $this->afterMaking(function (Delivery $delivery){
            $driver = Driver::make(['business_id'=>$delivery->business_id,'driver_name'=>'Alans Parsons']);
            $driver->save();
            $delivery->driver_id=$driver->id;
        });
    }

    public function withOrders()
    {
        return $this->afterCreating(function (Delivery $delivery){
            $orders = Order::whereBusinessId($delivery->business_id)->limit(10)->inRandomOrder()->get();
            if($orders->isEmpty()){
                $orders = Order::factory(10)->withProducts()->create();
            }

            $insertRecords=[];
            $i=1;
            foreach ($orders as $order){
                $insertRecords[]=[
                    'delivery_id'=>$delivery->id,
                    'order_id'=>$order->id,
                    'delivery_sequence'=>$i
                ];
                $i++;
            }
            DeliveryOrder::insert($insertRecords);
        });
    }
    public function configure()
    {
        return $this->afterMaking(function (Delivery $delivery){
            $business = null;

            if(empty($delivery->business_id)){
                $business = Business::inRandomOrder()->first();
                if(empty($business)){
                    $business = Business::factory()->create();
                }

                $delivery->business_id=$business->id;
            }

            if(empty($delivery->driver_id)){

                $business = $business??Business::find($delivery->business_id);
                $driver = Driver::where('business_id',$business->id)->inRandomOrder()->first();
                if(empty($driver)){
                    $driver = Driver::make(['business_id'=>$business->id,'name'=>'Alan Parsons']);
                }
                $delivery->driver_id = $driver->id;
            }

        });
    }
}

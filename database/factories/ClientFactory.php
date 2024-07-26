<?php

namespace Database\Factories;


use App\Models\Business;
use App\Models\Client;
use App\Models\SaasUser;
use App\Models\Order;

use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'=>fake()->firstName,
            'surname'=>fake()->lastName,
            'telephone'=>fake()->phoneNumber
        ];
    }

    public function withUser(SaasUser $user)
    {
        return $this->afterMaking(function (Client $client) use ($user){
            $client->saas_user_id=$user->id;
            $client->business_id = $user->business_id;
        });
    }

    public function withOrders()
    {
        return $this->afterCreating(function (Client $client){
            $user = SaasUser::find($client->saas_user_id);
            Order::factory(50)->withUser($user)->withProducts()->create(['client_id'=>$client->id]);
        });
    }
    public function configure():static
    {
        return $this->afterMaking(function (Client $client){
           if(empty($client->business_id)){

               if(!empty($client->saas_user_id)){
                  $user = SaasUser::find($client->saas_user_id);
               } else {
                  $user = SaasUser::factory()->create();
               }


               $client->saas_user_id = $user->id;
               $client->business_id = $user->business_id;

               return;
           }

           if(empty($client->saas_user_id)){
               $user = Business::find($client->business_id);
               $client->business_id = $user->business_id;
           }
        });
    }
}

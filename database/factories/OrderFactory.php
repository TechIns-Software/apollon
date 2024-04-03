<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\SaasUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status'=>'OPEN',
            'description'=>"Lorem Ipsum"
        ];
    }

    private function setUser(SaasUser $user,Order $order)
    {
        $order->saas_user_id = $user->id;
        $order->business_id = $user->business_id;
        $client = Client::whereBusinessId($user->business_id)->inRandomOrder()->first();
        if(empty($client)){
            $client = Client::factory()->withUser($user)->create();
        }
        $order->client_id = $client->id;
    }
    public function withUser(SaasUser $user)
    {
        return $this->afterMaking(function (Order $order) use ($user){
            $this->setUser($user,$order);
        });
    }

    public function withProducts()
    {
        return $this->afterCreating(function (Order $order) {

            $products = Product::inRandomOrder()
                ->where('business_id',$order->business_id)
                ->limit(10)
                ->get();
            if(empty($products->count())){
                $products = Product::factory(10)->create(['business_id'=>$order->business_id]);
            }
            foreach ($products as $product){
                ProductOrder::factory()
                    ->create([
                        'product_id'=>$product->id,
                        'order_id'=>$order->id
                    ]);
            }
        });
    }
    public function configure()
    {
        return $this->afterMaking(function (Order $order){
            $user = null;
            if(empty($order->user_id) || empty($order->business_id)){
                $user = SaasUser::inRandomOrder()->first();
                if(empty($user)){
                    $user = SaasUser::factory()->create();
                }

                $this->setUser($user,$order);
                return;
            }

            if(empty($order->client_id)){
                $client = Client::whereBusinessId($order->business_id)->inRandomOrder()->first();
                if(empty($client)){
                    $user = $user ?? SaasUser::find($client->saas_user_id);
                    $client = Client::factory()->withUser($user)->create();
                }

                $order->client_id = $client->id;
            }

        });
    }
}

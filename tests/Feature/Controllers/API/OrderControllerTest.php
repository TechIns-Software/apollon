<?php

namespace Feature\Controllers\API;

use App\Models\Business;
use App\Models\Client;
use App\Models\Order;
use App\Models\SaasUser;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testInsert()
    {
        $user = SaasUser::factory()->create();
        $client = Client::factory()->withUser($user)->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload = [
            'client_id'=>$client->id,
            'description'=>"Omae wa mou shindeiru",
            'status'=>'OPEN'
        ];

        $response=$this->post('/api/order',$payload);

        $json = $response->json();
        $response->assertStatus(201);

        $orderInDb = Order::find($json['id']);
        $this->assertNotEmpty($orderInDb);
        foreach ($json as $key => $value){
            if(in_array($key,['created_at','updated_at','deleted_at','id'])){
                continue;
            }

            $this->assertEquals($value,$orderInDb->$key);
        }

        $this->assertEquals($user->business_id,$orderInDb->business_id);
        $this->assertEquals($client->business_id,$orderInDb->business_id);

        $this->assertEquals($client->id,$orderInDb->client_id);
        $this->assertEquals($user->id,$orderInDb->saas_user_id);
    }

    public function testInsertWrongCLient()
    {
        $user = SaasUser::factory()->create();
        $business = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);
        $client = Client::factory()->withUser($user2)->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload = [
            'client_id'=>$client->id,
            'description'=>"Omae wa mou shindeiru",
            'status'=>'OPEN'
        ];

        $response=$this->post('/api/order',$payload);

        $response->assertStatus(400);

        $orders = Order::all()->toArray();
        $this->assertEmpty($orders);
    }

    public function testInsertInvalidClientId()
    {
        $user = SaasUser::factory()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload = [
            'client_id'=>-1,
            'description'=>"Omae wa mou shindeiru",
            'status'=>'OPEN'
        ];

        $response=$this->post('/api/order',$payload);

        $response->assertStatus(400);

        $orders = Order::all()->toArray();
        $this->assertEmpty($orders);
    }

    public function testInsertInvalidMissingCLientId()
    {
        $user = SaasUser::factory()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload = [
            'client_id'=>100,
            'description'=>"Omae wa mou shindeiru",
            'status'=>'OPEN'
        ];

        $response=$this->post('/api/order',$payload);

        $response->assertStatus(400);

        $orders = Order::all()->toArray();
        $this->assertEmpty($orders);
    }
}

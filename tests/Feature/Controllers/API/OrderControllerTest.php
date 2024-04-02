<?php

namespace Feature\Controllers\API;

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
}

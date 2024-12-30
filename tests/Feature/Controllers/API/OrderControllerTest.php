<?php

namespace Feature\Controllers\API;

use App\Models\Business;
use App\Models\Client;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\SaasUser;
use App\Models\ProductOrder;
use App\Models\DeliveryOrder;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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
            if(in_array($key,['created_at','updated_at','deleted_at','id','items'])){
                continue;
            }

            $this->assertEquals($value,$orderInDb->$key);
        }

        $this->assertEquals($user->business_id,$orderInDb->business_id);
        $this->assertEquals($client->business_id,$orderInDb->business_id);

        $this->assertEquals($client->id,$orderInDb->client_id);
        $this->assertEquals($user->id,$orderInDb->saas_user_id);

        $this->assertEmpty($json['items']);
    }
    public function testInsertNewOrderWithPrices()
    {
        $user = SaasUser::factory()->create();
        $client = Client::factory()->withUser($user)->create();
        $productToPlaceNewValue = Product::factory(5)->create(['business_id'=>$user->business_id]);

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload = [
            'client_id'=>$client->id,
            'description'=>"Omae wa mou shindeiru",
            'status'=>'OPEN',
            'items'=>[]
        ];

        foreach ($productToPlaceNewValue as $product){
            $payload['items'][$product->id]=12.33;
        }

        $response=$this->post('/api/order',$payload);

        $json = $response->json();
        $response->assertStatus(201);

        $orderInDb = Order::find($json['id']);
        $this->assertNotEmpty($orderInDb);
        foreach ($json as $key => $value){
            if(in_array($key,['created_at','updated_at','deleted_at','id','items'])){
                continue;
            }

            $this->assertEquals($value,$orderInDb->$key);
        }

        $this->assertEquals($user->business_id,$orderInDb->business_id);
        $this->assertEquals($client->business_id,$orderInDb->business_id);

        $this->assertEquals($client->id,$orderInDb->client_id);
        $this->assertEquals($user->id,$orderInDb->saas_user_id);

        $expectedProcustIds = $productToPlaceNewValue->pluck('id')->toArray();
        foreach ($json['items'] as $item){
            $this->assertContains($item['product_id'],$expectedProcustIds);
            $this->assertEquals($orderInDb->id,$item['order_id']);
            $this->assertEquals(12.33,$item['ammount']);

            $productOrder = ProductOrder::where('product_id',$item['product_id'])
                    ->where('order_id',$item['order_id'])
                    ->first();

            $this->assertNotEmpty($item);
            $this->assertEquals(12.33,$productOrder->ammount);
        }
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

    public function testInsertInvalidMissingClientId()
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

    public function testGetOrderNonExistent()
    {
        $user = SaasUser::factory()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->get('/api/order/1');

        $response->assertStatus(404);
    }

    public function testGetOrderBelongsToDifferentBusiness()
    {
        $user = SaasUser::factory()->create();

        $business = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);
        $order = Order::factory()->withUser($user2)->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->get('/api/order/'.$order->id);
        $response->assertStatus(403);
    }

    public function testEditOrderBelongsToDifferentBusiness()
    {
        $user = SaasUser::factory()->create();

        $business = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);
        $order = Order::factory()->withUser($user2)->create();
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

        $response = $this->post('/api/order/'.$order->id,$payload);
        $response->assertStatus(403);
    }

    public function testUpdateSuccess()
    {
        $user = SaasUser::factory()->create();

        $order = Order::factory()->withUser($user)->create();
        $client = Client::factory()->withUser($user)->create();

        $payload = [
            'client_id'=>$client->id,
            'description'=>"Omae wa mou shindeiru",
            'status'=>'FINISHED'
        ];

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->post('/api/order/'.$order->id,$payload);

        $json = $response->json();

        $response->assertStatus(200);

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

        $this->assertEquals("FINISHED",$orderInDb->status);
    }

    public function testUpdateSuccessOnlyStatus()
    {
        $user = SaasUser::factory()->create();

        $order = Order::factory()->withUser($user)->create();

        $payload = [
            'status'=>'FINISHED'
        ];

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->post('/api/order/'.$order->id,$payload);

        $json = $response->json();

        $response->assertStatus(200);

        $orderInDb = Order::find($json['id']);
        $this->assertNotEmpty($orderInDb);
        foreach ($json as $key => $value){
            if(in_array($key,['created_at','updated_at','deleted_at','id'])){
                continue;
            }

            $this->assertEquals($value,$orderInDb->$key);
        }

        $this->assertEquals($user->business_id,$orderInDb->business_id);
        $this->assertEquals($user->id,$orderInDb->saas_user_id);

        $this->assertEquals("FINISHED",$orderInDb->status);
    }
    public function testDeleteSuccessOnlyStatus()
    {
        $user = SaasUser::factory()->create();

        $order = Order::factory()->withUser($user)->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->delete('/api/order/'.$order->id);

        $response->assertStatus(200);

        $orderInDb = Order::find($order->id);
        $this->assertEmpty($orderInDb);

        $actualOrderInDb = DB::table('order')->where('id',$order->id)->limit(1)->first();
        $this->assertNotEmpty($actualOrderInDb);

        $this->assertNotEmpty($actualOrderInDb->deleted_at);
    }

    public function testDeleteSuccessOrderDoesNotBelongToBusiness()
    {
        $user = SaasUser::factory()->create();

        $business = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);
        $order = Order::factory()->withUser($user2)->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->delete('/api/order/'.$order->id);
        $response->assertStatus(403);

        $orderInDb = Order::find($order->id);
        $this->assertNotEmpty($orderInDb);

        $actualOrderInDb = DB::table('order')->where('id',$order->id)->limit(1)->first();
        $this->assertNotEmpty($actualOrderInDb);
        $this->assertEmpty($actualOrderInDb->deleted_at);
    }

    public function testAddPrices()
    {
        DB::statement("DELETE FROM ".SaasUser::TABLE);
        DB::statement("DELETE FROM `".Order::TABLE."`");
        DB::statement("DELETE FROM ".Product::TABLE);

        $business = Business::factory()->create();
        $user = SaasUser::factory()->create(['business_id'=>$business->id]);
        $order = Order::factory()->withUser($user)->withProducts()->create(['business_id'=>$business->id]);

        $productsToUpdate = Product::where('business_id',$business->id)->get();
        $productToPlaceNewValue = Product::factory(5)->create(['business_id'=>$business->id]);

        $payload = [];

        foreach ($productToPlaceNewValue as $value){
            $payload[$value->id]=12.2;
        }

        foreach ($productsToUpdate as $value){
            $payload[$value->id]=12.2;
        }

        $productsToSkip = Product::factory(5)->create(['business_id'=>$business->id])->pluck('id')->toArray();

        $productsIdToCheck = array_keys($payload);
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );
        $response = $this->post('/api/order/'.$order->id.'/products',['items'=>$payload]);
        $response->assertStatus(200);

        $values = ProductOrder::whereOrderId($order->id)->whereOrderId($order->id)->get();
        foreach ($values as $value){
            $this->assertEquals(12.2,(float)$value->ammount);
            $this->assertEquals($order->id,$value->order_id);
            $this->assertContains($value->product_id,$productsIdToCheck);
            $this->assertNotContains($value->product_id,$productsToSkip);
        }
    }

    public function testDeletePricesFromOrder()
    {
        DB::statement("DELETE FROM ".SaasUser::TABLE);
        DB::statement("DELETE FROM `".Order::TABLE."`");
        DB::statement("DELETE FROM ".Product::TABLE);

        $business = Business::factory()->create();
        $user = SaasUser::factory()->create(['business_id'=>$business->id]);
        $order = Order::factory()->withProducts()->create(['business_id'=>$business->id]);
        $productsToDelete =  Product::factory()->create(['business_id'=>$business->id]);

        ProductOrder::factory()->create(['product_id'=>$productsToDelete->id,'order_id'=>$order->id]);

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );


        $response = $this->delete('/api/order/'.$order->id.'/product/'.$productsToDelete->id);

        $response->assertStatus(200);

        $deletedProductExists = ProductOrder::where('order_id',$order->id)->where('product_id',$productsToDelete->id)->exists();
        $this->assertFalse($deletedProductExists);

        $existingProducts = ProductOrder::where('order_id',$order->id)->pluck('product_id')->count();
        $this->assertNotEquals(0,$existingProducts);
    }

    public function testGetOrdersWithoutDelivery()
    {
        $user = SaasUser::factory()->create();

        $delivery = Delivery::factory(2)->withOrders()->create(['business_id'=>$user->business_id]);
        $ordersWithDelivery = DeliveryOrder::pluck('order_id')->toArray();

        $orderWithoutDelivery = Order::factory()->withUser($user)->create();
        DeliveryOrder::where('order_id',$orderWithoutDelivery->id)->delete();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->get('/api/order?without_delivery=true');
        $responseBody = $response->json('data');
        $response->assertStatus(200);

        $this->assertCount(1,$responseBody);
        $responseOrder = $responseBody[0];
        $this->assertNotContains($responseOrder['id'],$ordersWithDelivery);
        $this->assertEquals($orderWithoutDelivery->id,$responseOrder['id']);
    }
    public function testGetOrdersWithoutDeliveryAsFalse()
    {
        $user = SaasUser::factory()->create();

        $delivery = Delivery::factory(2)->withOrders()->create(['business_id'=>$user->business_id]);
        $ordersWithDelivery = DeliveryOrder::pluck('order_id')->toArray();

        $orderWithoutDelivery = Order::factory()->withUser($user)->create();
        DeliveryOrder::where('order_id',$orderWithoutDelivery->id)->delete();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->get('/api/order?without_delivery=false');
        $responseBody = $response->json('data');
        $response->assertStatus(200);

        foreach ($responseBody as $deliveryInResponse){

            $this->assertNotEquals($orderWithoutDelivery->id,$deliveryInResponse['id']);
            $this->assertContains($deliveryInResponse['id'],$ordersWithDelivery);
        }
    }

    public function testValidateWrongWithoutDelivery()
    {
        $user = SaasUser::factory()->create();

        $delivery = Delivery::factory(2)->withOrders()->create(['business_id'=>$user->business_id]);
        $ordersWithDelivery = DeliveryOrder::pluck('order_id')->toArray();

        $orderWithoutDelivery = Order::factory()->withUser($user)->create();
        DeliveryOrder::where('order_id',$orderWithoutDelivery->id)->delete();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );
        $response = $this->get('/api/order?without_delivery=lalalala');
        $response->assertStatus(400);
        $response->assertJsonMissing(['data']);
    }

    public static function invalidOrderByParams()
    {
        return  [
            [[
                'ordering'=>'asc'
            ]],
            [[
                'ordering'=>'desc'
            ]],
            [[
                'order_by'=>'created_at',
                'ordering'=>"dsadsadas"
            ]],
            [[
                'order_by'=>'',
                'ordering'=>"asc"
            ]],
            [[
                'order_by'=>'',
                'ordering'=>"desc"
            ]],
            [[
                'order_by'=>null,
                'ordering'=>"desc"
            ]],
        ];

    }

    /**
     * @dataProvider invalidOrderByParams
     */
    public function testOrderByParamsReturnError400($params)
    {
        $user = SaasUser::factory()->create();
        Order::factory(2)->withUser($user)->create(['business_id'=>$user->business_id]);

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $result = $this->get('/api/order?'.http_build_query($params));
        $result->assertStatus(400);
    }

    public function testListOrderingClientName()
    {
        DB::statement("DELETE from `".Order::TABLE."`");
        DB::statement("DELETE from ".Client::TABLE);


        $user = SaasUser::factory()->create();

        $client = Client::factory()->withUser($user)->create([
            'name'=>"Αναξαγόρας",'surname'=>"Αυξεντίου",
            'business_id'=>$user->business_id
        ]);

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $order=Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
                'client_id'=>$client->id
            ]);

        $order->client_id = $order->client_id;
        $order->business_id = $user->business_id;
        $order->save();

        // Client 2
        $client2 = Client::factory()->withUser($user)->create([
            'name'=>"Αναξαγόρας",'surname'=>"Βουλγατάς",
            'business_id'=>$user->business_id
        ]);

        $order=Order::factory()->withUser($user)
            ->make([
                'business_id'=>$user->business_id,
                'client_id'=>$client2->id
            ]);

        // Upon configure a different Client may be set
        $order->client_id = $client2->id;
        $order->business_id = $user->business_id;
        $order->save();


        $order=Order::factory()->withUser($user)
            ->make([
                'business_id'=>$user->business_id,
                'client_id'=>$client2->id
            ]);

        // Upon configure a different Client may be set
        $order->client_id = $client2->id;
        $order->business_id = $user->business_id;
        $order->save();


        // Client 3
        $client3 = Client::factory()->withUser($user)->create([
            'business_id'=>$user->business_id,
            'name'=>"Θεόδωρος",
            'surname'=>"Μαριάνου",
        ]);


        $order=Order::factory()->withUser($user)
            ->make([
                'business_id'=>$user->business_id,
                'client_id'=>$client3->id
            ]);

        // Upon configure a different Client may be set
        $order->client_id = $client3->id;
        $order->business_id = $user->business_id;
        $order->save();


        // Upon configure a different Client may be set
        $order=Order::factory()->withUser($user)
            ->make([
                'business_id'=>$user->business_id,
                'client_id'=>$client3->id
            ]);
        $order->client_id = $client3->id;
        $order->business_id = $user->business_id;
        $order->save();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $results = $this->get('/api/order?order_by=client_name&ordering=asc&limit=100');
        $results->assertStatus(200);

        $data = $results->json('data');

        $names =  array_map(fn ($item) => ['client_name'=>$item['client_name'],'client_surname'=>$item['client_surname']], $data);

        $expectedNameSequence = [
            [
              'client_name' => 'Αναξαγόρας',
              'client_surname' => 'Αυξεντίου',
            ],
            [
                'client_name' => 'Αναξαγόρας',
                'client_surname' => 'Αυξεντίου',
            ],
            [
                'client_name' => 'Αναξαγόρας',
                'client_surname' => 'Βουλγατάς',
            ],
            [
                'client_name' => 'Αναξαγόρας',
                'client_surname' => 'Βουλγατάς',
            ],
            [
                'client_name' => 'Θεόδωρος',
                'client_surname' => 'Μαριάνου',
            ],
            [
                'client_name' => 'Θεόδωρος',
                'client_surname' => 'Μαριάνου',
            ],
        ];

        $this->assertEquals($expectedNameSequence,$names);
    }

    public function testListOrderingClientNameDesc()
    {
        DB::statement("DELETE from `".Order::TABLE."`");
        DB::statement("DELETE from ".Client::TABLE);


        $user = SaasUser::factory()->create();

        $client = Client::factory()->withUser($user)->create([
            'name'=>"Αναξαγόρας",'surname'=>"Αυξεντίου",
            'business_id'=>$user->business_id
        ]);

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $order=Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
                'client_id'=>$client->id
            ]);

        $order->client_id = $order->client_id;
        $order->business_id = $user->business_id;
        $order->save();

        // Client 2
        $client2 = Client::factory()->withUser($user)->create([
            'name'=>"Αναξαγόρας",'surname'=>"Βουλγατάς",
            'business_id'=>$user->business_id
        ]);

        $order=Order::factory()->withUser($user)
            ->make([
                'business_id'=>$user->business_id,
                'client_id'=>$client2->id
            ]);

        // Upon configure a different Client may be set
        $order->client_id = $client2->id;
        $order->business_id = $user->business_id;
        $order->save();


        $order=Order::factory()->withUser($user)
            ->make([
                'business_id'=>$user->business_id,
                'client_id'=>$client2->id
            ]);

        // Upon configure a different Client may be set
        $order->client_id = $client2->id;
        $order->business_id = $user->business_id;
        $order->save();


        // Client 3
        $client3 = Client::factory()->withUser($user)->create([
            'business_id'=>$user->business_id,
            'name'=>"Θεόδωρος",
            'surname'=>"Μαριάνου",
        ]);


        $order=Order::factory()->withUser($user)
            ->make([
                'business_id'=>$user->business_id,
                'client_id'=>$client3->id
            ]);

        // Upon configure a different Client may be set
        $order->client_id = $client3->id;
        $order->business_id = $user->business_id;
        $order->save();


        // Upon configure a different Client may be set
        $order=Order::factory()->withUser($user)
            ->make([
                'business_id'=>$user->business_id,
                'client_id'=>$client3->id
            ]);
        $order->client_id = $client3->id;
        $order->business_id = $user->business_id;
        $order->save();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $results = $this->get('/api/order?order_by=client_name&ordering=desc&limit=100');
        $results->assertStatus(200);

        $data = $results->json('data');

        $names =  array_map(fn ($item) => ['client_name'=>$item['client_name'],'client_surname'=>$item['client_surname']], $data);

        $expectedNameSequence = array_reverse([
            [
                'client_name' => 'Αναξαγόρας',
                'client_surname' => 'Αυξεντίου',
            ],
            [
                'client_name' => 'Αναξαγόρας',
                'client_surname' => 'Αυξεντίου',
            ],
            [
                'client_name' => 'Αναξαγόρας',
                'client_surname' => 'Βουλγατάς',
            ],
            [
                'client_name' => 'Αναξαγόρας',
                'client_surname' => 'Βουλγατάς',
            ],
            [
                'client_name' => 'Θεόδωρος',
                'client_surname' => 'Μαριάνου',
            ],
            [
                'client_name' => 'Θεόδωρος',
                'client_surname' => 'Μαριάνου',
            ],
        ]);

        $this->assertEquals($expectedNameSequence,$names);
    }

    public function testListOrderingArea()
    {
        DB::statement("DELETE from `".Order::TABLE."`");
        DB::statement("DELETE from ".Client::TABLE);


        $user = SaasUser::factory()->create();

        $client = Client::factory()->withUser($user)->create([
            'name'=>"Αναξαγόρας",'surname'=>"Αυξεντίου",
            'nomos'=>"ΑΤΤΙΚΗΣ",'region'=>"ΑΧΑΡΝΕΣ",
            'business_id'=>$user->business_id
        ]);

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $client = Client::factory()->withUser($user)->create([
            'name'=>"Μπάμπης",'surname'=>"Σουγιάς",
            'nomos'=>"Λασιθίου",'region'=>"Μάταλα",
            'business_id'=>$user->business_id
        ]);

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();


        $client = Client::factory()->withUser($user)->create([
            'name'=>"Μπάμπης",'surname'=>"Σουγιάς",
            'nomos'=>"Ηλίας",'region'=>"Πύργος",
            'business_id'=>$user->business_id
        ]);

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $client = Client::factory()->withUser($user)->create([
            'name'=>"Μπάμπης",'surname'=>"Σουγιάς",
            'nomos'=>"Ηλίας",'region'=>"Ολυμπία",
            'business_id'=>$user->business_id
        ]);

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $regionOrdering = [
            [
                'client_nomos'=>'ΑΤΤΙΚΗΣ',
                'client_region'=>'ΑΧΑΡΝΕΣ'
            ],
            [
                'client_nomos'=>'ΑΤΤΙΚΗΣ',
                'client_region'=>'ΑΧΑΡΝΕΣ'
            ],
            // Area or ordered first then nomos.
            [
                'client_nomos'=>'Λασιθίου',
                'client_region'=>'Μάταλα'
            ],
            [
                'client_nomos'=>'Λασιθίου',
                'client_region'=>'Μάταλα'
            ],
            [
                'client_nomos'=>'Ηλίας',
                'client_region'=>'Ολυμπία'
            ],
            [
                'client_nomos'=>'Ηλίας',
                'client_region'=>'Πύργος'
            ],
            [
                'client_nomos'=>'Ηλίας',
                'client_region'=>'Πύργος'
            ],
        ];

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $results = $this->get('/api/order?order_by=area&ordering=asc&limit=100');
        $results->assertStatus(200);

        $data = $results->json('data');

        $regions =  array_map(fn ($item) => ['client_nomos'=>$item['client_nomos'],'client_region'=>$item['client_region']], $data);
        $this->assertEquals($regionOrdering,$regions);
    }

    public function testListOrderingAreaDesc()
    {
        DB::statement("DELETE from `".Order::TABLE."`");
        DB::statement("DELETE from ".Client::TABLE);


        $user = SaasUser::factory()->create();

        $client = Client::factory()->withUser($user)->create([
            'name'=>"Αναξαγόρας",'surname'=>"Αυξεντίου",
            'nomos'=>"ΑΤΤΙΚΗΣ",'region'=>"ΑΧΑΡΝΕΣ",
            'business_id'=>$user->business_id
        ]);

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $client = Client::factory()->withUser($user)->create([
            'name'=>"Μπάμπης",'surname'=>"Σουγιάς",
            'nomos'=>"Λασιθίου",'region'=>"Μάταλα",
            'business_id'=>$user->business_id
        ]);

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();


        $client = Client::factory()->withUser($user)->create([
            'name'=>"Μπάμπης",'surname'=>"Σουγιάς",
            'nomos'=>"Ηλίας",'region'=>"Πύργος",
            'business_id'=>$user->business_id
        ]);

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $client = Client::factory()->withUser($user)->create([
            'name'=>"Μπάμπης",'surname'=>"Σουγιάς",
            'nomos'=>"Ηλίας",'region'=>"Ολυμπία",
            'business_id'=>$user->business_id
        ]);

        $order = Order::factory()->withUser($user)
            ->create([
                'business_id'=>$user->business_id,
            ]);

        $order->client_id = $client->id;
        $order->business_id = $user->business_id;
        $order->save();

        $regionOrdering = [
            [
                'client_nomos'=>'ΑΤΤΙΚΗΣ',
                'client_region'=>'ΑΧΑΡΝΕΣ'
            ],
            [
                'client_nomos'=>'ΑΤΤΙΚΗΣ',
                'client_region'=>'ΑΧΑΡΝΕΣ'
            ],
            // Area or ordered first then nomos.
            [
                'client_nomos'=>'Λασιθίου',
                'client_region'=>'Μάταλα'
            ],
            [
                'client_nomos'=>'Λασιθίου',
                'client_region'=>'Μάταλα'
            ],
            [
                'client_nomos'=>'Ηλίας',
                'client_region'=>'Ολυμπία'
            ],
            [
                'client_nomos'=>'Ηλίας',
                'client_region'=>'Πύργος'
            ],
            [
                'client_nomos'=>'Ηλίας',
                'client_region'=>'Πύργος'
            ],
        ];

        $regionOrdering = array_reverse($regionOrdering);

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $results = $this->get('/api/order?order_by=area&ordering=desc&limit=100');
        $results->assertStatus(200);

        $data = $results->json('data');

        $regions =  array_map(fn ($item) => ['client_nomos'=>$item['client_nomos'],'client_region'=>$item['client_region']], $data);
        $this->assertEquals($regionOrdering,$regions);
    }

    public function testListFilterById()
    {
        DB::statement("DELETE from `".Order::TABLE."`");
        DB::statement("DELETE from ".Client::TABLE);

        $user = SaasUser::factory()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $client = Client::factory()->withUser($user)->create([
            'name'=>"Αναξαγόρας",'surname'=>"Αυξεντίου",
            'nomos'=>"ΑΤΤΙΚΗΣ",'region'=>"ΑΧΑΡΝΕΣ",
            'business_id'=>$user->business_id
        ]);

        $order1 = Order::factory()->withUser($user)->create([
            'business_id'=>$user->business_id,
            'description'=>"",
            'client_id'=>$client->id,
            'id'=>2022
        ]);

        $order2 = Order::factory()->withUser($user)->create([
            'business_id'=>$user->business_id,
            'description'=>"",
            'client_id'=>$client->id,
            'id'=>2202
        ]);

        $order3 = Order::factory()->withUser($user)->create([
            'business_id'=>$user->business_id,
            'description'=>"",
            'client_id'=>$client->id,
            'id'=>1022
        ]);

        $order4 = Order::factory()->withUser($user)->create([
            'business_id'=>$user->business_id,
            'description'=>"",
            'client_id'=>$client->id,
            'id'=>899
        ]);

        $order5 = Order::factory()->withUser($user)->create([
            'business_id'=>$user->business_id,
            'description'=>"",
            'client_id'=>$client->id,
            'id'=>898
        ]);

        $validIds = [$order1->id,$order2->id,$order3->id];
        $invalidIds = [$order4->id,$order5->id];

        $result = $this->get('/api/order?searchterm=22');
        $result->assertStatus(200);

        $data = $result->json('data');
        foreach ($data as $item){
            $this->assertContains($item['id'],$validIds);
            $this->assertNotContains($item['id'],$invalidIds);
        }
    }
}

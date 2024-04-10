<?php

namespace Feature\Controllers\API;

use App\Models\Business;
use App\Models\Delivery;
use App\Models\DeliveryOrder;
use App\Models\Driver;
use App\Models\Order;
use App\Models\SaasUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeliveryControllerTest extends TestCase
{
    use RefreshDatabase;
    public function testAddWithoutDriver()
    {
        $user = SaasUser::factory()->create();
        $orders = Order::factory(5)->withUser($user)->withProducts()->create();

        $orderIds = [];
        foreach ($orders as $order) {
            $orderIds[] = $order->id;
        }

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'driver_name'=>"Test",
            'delivery_date'=>'2025-12-01',
            'name'=>'Panzer Delivery',
            'orders'=>$orderIds
        ];

        $response = $this->post('/api/delivery',$payload);
        $body=$response->json();
        $response->assertStatus(201);

        $delivery=Delivery::find($body['id']);

        $driver = Driver::find($delivery->driver_id);
        $this->assertNotEmpty($driver);
        $this->assertEquals($user->business_id,$driver->business_id);

        $this->assertNotEmpty($delivery);
        $this->assertEquals($user->business_id,$delivery->business_id);
        $this->assertEquals($body['business_id'],$delivery->business_id);
        $this->assertEquals($payload['name'],$delivery->name);

        foreach ($body['orders'] as $order) {
            $order=$order['order'];
            $deliveryOrder = DeliveryOrder::whereOrderId($order['id'])
                ->whereDeliveryId($delivery->id)
                ->first();

            $this->assertNotEmpty($deliveryOrder);
            $order = Order::find($deliveryOrder->order_id);

            $this->assertNotEmpty($order);
            $this->assertEquals($deliveryOrder->id,$order->id);
            $this->assertEquals($user->business_id,$order->business_id);
        }
    }

    public function testAddWithExistingDriver()
    {
        $user = SaasUser::factory()->create();
        $orders = Order::factory(5)->withUser($user)->withProducts()->create();

        $driver = Driver::create([
            'driver_name'=>'lalalala',
            'business_id'=>$user->business_id,
        ]);

        $orderIds = [];
        foreach ($orders as $order) {
            $orderIds[] = $order->id;
        }

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'driver_id'=>$driver->id,
            'delivery_date'=>'2025-12-01',
            'name'=>'Panzer Delivery',
            'orders'=>$orderIds
        ];


        $response = $this->post('/api/delivery',$payload);
        $body=$response->json();
        $response->assertStatus(201);

        $delivery=Delivery::find($body['id']);
        $this->assertNotEmpty($delivery);
        $this->assertEquals($user->business_id,$delivery->business_id);
        $this->assertEquals($body['business_id'],$delivery->business_id);
        $this->assertEquals($payload['name'],$delivery->name);

        foreach ($body['orders'] as $order) {
            $order=$order['order'];
            $deliveryOrder = DeliveryOrder::whereOrderId($order['id'])
                ->whereDeliveryId($delivery->id)
                ->first();

            $this->assertNotEmpty($deliveryOrder);
            $order = Order::find($deliveryOrder->order_id);

            $this->assertNotEmpty($order);
            $this->assertEquals($deliveryOrder->id,$order->id);
            $this->assertEquals($user->business_id,$order->business_id);
        }
    }

    public function testAddWithoutOrders()
    {
        $user = SaasUser::factory()->create();
        $driver = Driver::create([
            'driver_name'=>'lalalala',
            'business_id'=>$user->business_id,
        ]);

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'driver_id'=>$driver->id,
            'delivery_date'=>'2025-12-01',
            'name'=>'Panzer Delivery',
        ];

        $response = $this->post('/api/delivery',$payload);

        $body=$response->json();
        $response->assertStatus(201);

        $delivery=Delivery::find($body['id']);
        $this->assertNotEmpty($delivery);
        $this->assertEquals($user->business_id,$delivery->business_id);
        $this->assertEquals($body['business_id'],$delivery->business_id);

        $this->assertEmpty($body['orders']);

        $deliveryOrder = DeliveryOrder::whereDeliveryId($delivery->id)->get();
        $this->assertEmpty($deliveryOrder);
    }

    public function testEdit()
    {
        $user = SaasUser::factory()->create();
        $driver = Driver::create([
            'driver_name'=>'lalalala',
            'business_id'=>$user->business_id,
        ]);

        $orders = Order::factory(5)->withUser($user)->withProducts()->create();
        $orderIds = [];
        foreach ($orders as $order) {
            $orderIds[] = $order->id;
        }

        $delivery = Delivery::factory()->businessFromUser($user)->withNewDriver()->create();

        $orders2 = Order::factory(5)->withUser($user)->withProducts()->create();

        $unmodifiedOrders=[];
        foreach ($orders2 as $order) {
            $unmodifiedOrders[] = $order->id;
        }

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'driver_id'=>$driver->id,
            'delivery_date'=>'2025-12-01',
            'name'=>'Panzer Delivery',
            'orders'=>$orderIds
        ];
        $response = $this->post('/api/delivery/'.$delivery->id,$payload);
        $body=$response->json();
        $response->assertStatus(200);


        $deliveryInDb=Delivery::find($body['id']);
        $this->assertNotEmpty($deliveryInDb);

        $this->assertEquals($user->business_id,$delivery->business_id);
        $this->assertEquals($body['business_id'],$delivery->business_id);
        $this->assertEquals($deliveryInDb->business_id,$delivery->business_id);


        $this->assertNotEmpty($body['orders']);

        $deliveryOrder = DeliveryOrder::whereDeliveryId($delivery->id)->get();
        $this->assertNotEmpty($deliveryOrder);

        foreach ($body['orders'] as $order) {
            $order=$order['order'];
            $deliveryOrder = DeliveryOrder::whereOrderId($order['id'])
                ->whereDeliveryId($delivery->id)
                ->first();

            $this->assertNotEmpty($deliveryOrder);
            $order = Order::find($deliveryOrder->order_id);

            $this->assertNotEmpty($order);
            $this->assertEquals($deliveryOrder->id,$order->id);
            $this->assertEquals($user->business_id,$order->business_id);
            $this->assertContains($order->id,$orderIds);
            $this->assertNotContains($order->id,$unmodifiedOrders);
        }
    }

    public function testEditWrongDriver()
    {
        $user = SaasUser::factory()->create();
        $delivery = Delivery::factory()->businessFromUser($user)->withNewDriver()->create();

        $driverId=-100;
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );
        $response = $this->post('/api/delivery/'.$delivery->id,['driver_id'=>$driverId]);
        $response->assertStatus(400);
    }

    public function testEditMissingDelivery()
    {
        $user = SaasUser::factory()->create();
        $driver = Driver::create([
            'driver_name'=>'lalalala',
            'business_id'=>$user->business_id,
        ]);

        $orders = Order::factory(5)->withUser($user)->withProducts()->create();
        $orderIds = [];
        foreach ($orders as $order) {
            $orderIds[] = $order->id;
        }

        Delivery::factory()->businessFromUser($user)->withNewDriver()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'driver_id'=>$driver->id,
            'delivery_date'=>'2025-12-01',
            'name'=>'Panzer Delivery',
            'orders'=>$orderIds
        ];
        $response = $this->post('/api/delivery/256',$payload);
        $response->assertStatus(404);
    }

    public function testGetMissingDelivery()
    {
        $user = SaasUser::factory()->create();
        Delivery::factory()->businessFromUser($user)->withNewDriver()->create();
        Order::factory(5)->withUser($user)->withProducts()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->get('/api/delivery/256');
        $response->assertStatus(404);
    }


    public function testListSearch()
    {
        $user = SaasUser::factory()->create();
        $group1=Delivery::factory(5)->businessFromUser($user)->withNewDriver()->create(['name'=>"Τιμή Στα ελληνικά "]);
        $group2=Delivery::factory(5)->businessFromUser($user)->withNewDriver()->create(['name'=>"Something"]);
        $group3=Delivery::factory(5)->businessFromUser($user)->withNewDriver()->create(['name'=>"Something Else"]);
        $group4=Delivery::factory(5)->businessFromUser($user)->withNewDriver()->create(['name'=>"Ελληνική Τιμή"]);

        $missingBusiness = Business::factory()->create();
        $missinsOrder = Order::factory()->create(['business_id'=>$missingBusiness->id]);

        $searchtermAndExpectedValues=[
          'ελλην'=>array_merge($group1->pluck('id')->toArray(),$group4->pluck('id')->toArray()),
          'some'=>array_merge($group2->pluck('id')->toArray(),$group3->pluck('id')->toArray())
        ];

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        foreach ($searchtermAndExpectedValues as $searchterm=> $searchtermAndExpectedValue) {
            $response = $this->get('/api/delivery?name='.$searchterm);
            $response->assertStatus(200);
            $body = $response->json();
            foreach ($body['data'] as $result){
                $this->assertContains((int)$result['id'],$searchtermAndExpectedValue);
                $this->assertNotEquals($missinsOrder->id,(int)$result['id']);
            }
        }
    }
}

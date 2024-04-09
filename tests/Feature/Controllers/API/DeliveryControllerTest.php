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
}

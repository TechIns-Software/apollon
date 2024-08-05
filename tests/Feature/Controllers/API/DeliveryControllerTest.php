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
use Illuminate\Support\Facades\DB;
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
            'orders'=>$orderIds,
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
        $this->assertEquals($payload['delivery_date'],$delivery->delivery_date);
        $sequence = -1;
        foreach ($body['orders'] as $order) {
            $sequenceInResponse=$order['sequence'];
            $order=$order['order'];
            $deliveryOrder = DeliveryOrder::whereOrderId($order['id'])
                ->whereDeliveryId($delivery->id)
                ->first();

            $this->assertNotEmpty($deliveryOrder);
            $orderInDb = Order::find($deliveryOrder->order_id);
            $this->assertNotEmpty($order);
            $this->assertEquals($user->business_id,$orderInDb->business_id);
            $this->assertGreaterThanOrEqual($sequence,$sequenceInResponse);
            $this->assertEquals($sequenceInResponse,$deliveryOrder->delivery_sequence);
            $sequence=$sequenceInResponse;
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
        $this->assertEquals($payload['delivery_date'],$delivery->delivery_date);
        $this->assertEquals('2025-12-01',$delivery->delivery_date);

        $sequence = -1;
        foreach ($body['orders'] as $order) {
            $sequenceInResponse=$order['sequence'];

            $order=$order['order'];
            $deliveryOrder = DeliveryOrder::whereOrderId($order['id'])
                ->whereDeliveryId($delivery->id)
                ->first();

            $this->assertNotEmpty($deliveryOrder);
            $orderInDB = Order::find($deliveryOrder->order_id);

            $this->assertNotEmpty($order);
            $this->assertEquals($user->business_id,$orderInDB->business_id);

            $this->assertGreaterThanOrEqual($sequence,$sequenceInResponse);
            $this->assertEquals($sequenceInResponse,$deliveryOrder->delivery_sequence);
            $sequence=$sequenceInResponse;
        }
    }

    public function testInsertDuplicateOrderIds()
    {
        $user = SaasUser::factory()->create();
        $orders = Order::factory(5)->withUser($user)->create();

        $duplicateOrder= Order::factory()->withUser($user)->create();

        $driver = Driver::create([
            'driver_name'=>'lalalala',
            'business_id'=>$user->business_id,
        ]);

        $orderIds = [$duplicateOrder->id];
        foreach ($orders as $order) {
            $orderIds[] = $order->id;
        }

        $orderIds[]=$duplicateOrder->id;

        $expectedDuplicateOrderPriority= array_key_last($orderIds)+1;

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

        $insertedId = (int)$body['id'];

        $duplicateOrderCount = DeliveryOrder::whereDeliveryId($insertedId)->whereOrderId($duplicateOrder->id)->count();
        $this->assertEquals(1,$duplicateOrderCount);

        $deliveryOrderInDb = DeliveryOrder::whereDeliveryId($insertedId)->whereOrderId($duplicateOrder->id)->first();

        $this->assertNotEmpty($deliveryOrderInDb);
        $this->assertEquals($expectedDuplicateOrderPriority,$deliveryOrderInDb->delivery_sequence);
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
        $this->assertEquals($payload['delivery_date'],$delivery->delivery_date);
        $this->assertEquals('2025-12-01',$delivery->delivery_date);

        $this->assertEmpty($body['orders']);

        $deliveryOrder = DeliveryOrder::whereDeliveryId($delivery->id)->get();
        $this->assertEmpty($deliveryOrder);
    }

    public function testAddWrongDate()
    {
        DB::statement('DELETE from `'.Delivery::TABLE.'`');
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
            'delivery_date'=>'dsadsadsa',
            'name'=>'Panzer Delivery',
        ];

        $response = $this->post('/api/delivery',$payload);

        $response->assertStatus(400);

        $deliveries = Delivery::count();
        $this->assertEquals(0, $deliveries);
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
            'name'=>'Panzer Delivery',
            'orders'=>$orderIds,
            'delivery_date'=>'2025-12-01',
        ];
        $response = $this->post('/api/delivery/'.$delivery->id,$payload);
        $body=$response->json();
        $response->assertStatus(200);


        $deliveryInDb=Delivery::find($body['id']);
        $this->assertNotEmpty($deliveryInDb);

        $this->assertEquals($user->business_id,$delivery->business_id);
        $this->assertEquals($body['business_id'],$delivery->business_id);
        $this->assertEquals($deliveryInDb->business_id,$delivery->business_id);
        $this->assertEquals('2025-12-01',$deliveryInDb->delivery_date);


        $this->assertNotEmpty($body['orders']);

        $deliveryOrder = DeliveryOrder::whereDeliveryId($delivery->id)->get();
        $this->assertNotEmpty($deliveryOrder);

        foreach ($body['orders'] as $order) {
            $order=$order['order'];
            $deliveryOrder = DeliveryOrder::whereOrderId($order['id'])
                ->whereDeliveryId($delivery->id)
                ->first();

            $this->assertNotEmpty($deliveryOrder);
            $orderInDB = Order::find($deliveryOrder->order_id);

            $this->assertNotEmpty($order);

            $this->assertEquals($user->business_id,$orderInDB->business_id);
            $this->assertContains($orderInDB->id,$orderIds);
            $this->assertNotContains($orderInDB->id,$unmodifiedOrders);
        }
    }

    public function testEditWithNewOrders()
    {
        $user = SaasUser::factory()->create();
        $delivery = Delivery::factory()->businessFromUser($user)->withNewDriver()->create();

        $existingOrders =  Order::factory(5)->withUser($user)->withProducts()->create();

        foreach ($existingOrders as $key=>$order) {
            DeliveryOrder::create([
               'delivery_id'=>$delivery->id,
               'order_id'=>$order->id,
               'delivery_sequence'=>$key+1,
            ]);
        }

        $ordersToInsert = Order::factory(5)->withUser($user)->withProducts()->create();
        $orderIdsToInsert = $ordersToInsert->pluck('id');

        $payload = [
           'orders'=>$orderIdsToInsert->toArray(),
        ];
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->post('/api/delivery/'.$delivery->id,$payload);
        $response->assertStatus(200);

        foreach ($existingOrders as $order) {
            $existingOrder=DeliveryOrder::whereOrderId($order->id)->whereDeliveryId($delivery->id)->first();
            $this->assertEmpty($existingOrder);
        }

        foreach ($orderIdsToInsert as $key=>$order) {
            $existingOrder=DeliveryOrder::whereOrderId($order)->whereDeliveryId($delivery->id)->first();
            $this->assertNotEmpty($existingOrder);
            $this->assertEquals($key+1,$existingOrder->delivery_sequence);
        }
    }

    public function testEditWithoutModifyingOrders()
    {
        $user = SaasUser::factory()->create();
        $delivery = Delivery::factory()->businessFromUser($user)->withNewDriver()->create();

        $existingOrders =  Order::factory(5)->withUser($user)->withProducts()->create();

        foreach ($existingOrders as $key=>$order) {
            DeliveryOrder::create([
                'delivery_id'=>$delivery->id,
                'order_id'=>$order->id,
                'delivery_sequence'=>$key+1,
            ]);
        }

        $payload=[
            'name'=>"dsadsadas"
        ];
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->post('/api/delivery/'.$delivery->id,$payload);
        $response->assertStatus(200);

        $orderCount = DeliveryOrder::whereOrderId($order->id)->whereDeliveryId($delivery->id)->count();
        $this->assertNotEquals(0,$orderCount);
    }

    public function testEditRemoveOrders()
    {
        $user = SaasUser::factory()->create();
        $delivery = Delivery::factory()->businessFromUser($user)->withNewDriver()->create();

        $existingOrders =  Order::factory(5)->withUser($user)->withProducts()->create();

        foreach ($existingOrders as $key=>$order) {
            DeliveryOrder::create([
                'delivery_id'=>$delivery->id,
                'order_id'=>$order->id,
                'delivery_sequence'=>$key+1,
            ]);
        }

        $payload=[
            'name'=>"dsadsadas",
            'orders'=>[]
        ];
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->post('/api/delivery/'.$delivery->id,$payload);
        $response->assertStatus(200);

        $orderCount = DeliveryOrder::whereOrderId($order->id)->whereDeliveryId($delivery->id)->count();
        $this->assertEquals(0,$orderCount);
    }

    public function testEditRemoveOrdersNull()
    {
        $user = SaasUser::factory()->create();
        $delivery = Delivery::factory()->businessFromUser($user)->withNewDriver()->create();

        $existingOrders =  Order::factory(5)->withUser($user)->withProducts()->create();

        foreach ($existingOrders as $key=>$order) {
            DeliveryOrder::create([
                'delivery_id'=>$delivery->id,
                'order_id'=>$order->id,
                'delivery_sequence'=>$key+1,
            ]);
        }

        $payload=[
            'name'=>"dsadsadas",
            'orders'=>null
        ];
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->post('/api/delivery/'.$delivery->id,$payload);
        $response->assertStatus(200);

        $orderCount = DeliveryOrder::whereOrderId($order->id)->whereDeliveryId($delivery->id)->count();
        $this->assertEquals(0,$orderCount);
    }

    public function testEditWrongDeliveryDate()
    {
        $user = SaasUser::factory()->create();
        $delivery = Delivery::factory()->businessFromUser($user)->withNewDriver()->create(['delivery_date'=>'2026-12-12']);
        $payload=[
            'delivery_date'=>'dsasadasd',
        ];

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );
        $response = $this->post('/api/delivery/'.$delivery->id,$payload);
        $response->assertStatus(400);

        $deliveryInDb = Delivery::find($delivery->id);
        $this->assertNotEmpty($deliveryInDb);
        $this->assertEquals('2026-12-12',$deliveryInDb->delivery_date);
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

    public function testDelete()
    {
        $user = SaasUser::factory()->create();
        $delivery=Delivery::factory()->businessFromUser($user)->withNewDriver()->create(['name'=>"Τιμή Στα ελληνικά "]);

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );
        $response = $this->delete('/api/delivery/'.$delivery->id);

        $response->assertStatus(200);

        $deliveryInDbViaEloquent = Delivery::find($delivery->id);
        $this->assertEmpty($deliveryInDbViaEloquent);

        $actualRecord = DB::table('delivery')->where('id',$delivery->id)->first();

        $this->assertNotEmpty($actualRecord->deleted_at);
    }

    public function testUpdateSequence()
    {
        $user = SaasUser::factory()->create();
        $delivery=Delivery::factory()->businessFromUser($user)->create(['name'=>"Τιμή Στα ελληνικά "]);
        $order1 = Order::factory()->withUser($user)->create();
        $order2 = Order::factory()->withUser($user)->create();

        $deliveryOrder = new DeliveryOrder();
        $deliveryOrder->order_id = $order1->id;
        $deliveryOrder->delivery_id = $delivery->id;
        $deliveryOrder->delivery_sequence=1;
        $deliveryOrder->save();

        $deliveryOrder2 = new DeliveryOrder();
        $deliveryOrder2->order_id = $order2->id;
        $deliveryOrder2->delivery_id = $delivery->id;
        $deliveryOrder2->delivery_sequence=2;
        $deliveryOrder2->save();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->post('/api/delivery/order/'.$deliveryOrder->id,[
            'delivery_order'=>3
        ]);

        $body = $response->json();
        $response->assertStatus(200);


        $this->assertEquals(3,$body['sequence']);

        $orderSequenceInDb = DeliveryOrder::find($body['id']);
        $this->assertNotEmpty($orderSequenceInDb);

        $this->assertEquals(3,$orderSequenceInDb->delivery_sequence);

        $order2SequenceInDb=DeliveryOrder::find($deliveryOrder2->id);
        $this->assertEquals(2,$order2SequenceInDb->delivery_sequence);
    }
}

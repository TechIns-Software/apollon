<?php

namespace Tests\Unit\Models;

use App\Exceptions\BusinessIdIsNotSameAsUsersOne;
use App\Models\Business;
use App\Models\Client;
use App\Models\SaasUser;
use App\Models\Order;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\Exception;
use Tests\TestCase;
class OrderTest extends TestCase
{
    use RefreshDatabase;
    public function testSaveSuccessSingleUser()
    {
        $user = SaasUser::factory()->create();
        $client = Client::factory()->withUser($user)->create();

        $order = new Order();
        $order->business_id = $user->business_id;
        $order->saas_user_id = $user->id;
        $order->client_id = $client->id;

        try {
            $order->save();
        } catch (Exception $e){
            $this->fail();
        }

        $orderInDB = Order::find($order->id);
        $this->assertEquals($user->id , $orderInDB->saas_user_id);
        $this->assertEquals($user->business_id , $orderInDB->business_id);
        $this->assertEquals($client->business_id , $orderInDB->business_id);
        $this->assertEquals($client->id , $orderInDB->client_id);
    }

    public function testSaveSuccessDifferentUserSameBusiness()
    {
        $user = SaasUser::factory()->create();
        $client = Client::factory()->withUser($user)->create();

        $user2 = SaasUser::factory()->create(['business_id'=>$user->business_id]);

        $order = new Order();
        $order->business_id = $user->business_id;
        $order->saas_user_id = $user2->id;
        $order->client_id = $client->id;

        try {
            $order->save();
        } catch (Exception $e){
            $this->fail();
        }

        $orderInDB = Order::find($order->id);
        $this->assertEquals($user2->id , $orderInDB->saas_user_id);
        $this->assertNotEquals($user->id , $orderInDB->saas_user_id);

        $this->assertEquals($user->business_id , $orderInDB->business_id);
        $this->assertEquals($user2->business_id , $orderInDB->business_id);
        $this->assertEquals($client->business_id , $orderInDB->business_id);

        $this->assertEquals($client->id , $orderInDB->client_id);
    }

    public function testSaveSuccessDifferentUserDifferentBusiness()
    {
        $user = SaasUser::factory()->create();
        $client = Client::factory()->withUser($user)->create();

        $business = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);

        $order = new Order();
        $order->business_id = $user->business_id;
        $order->saas_user_id = $user2->id;
        $order->client_id = $client->id;

        // If static::saving upon model throws whatever exception laravel throws an \Error exception instead.
        $this->expectException(\Error::class);
        $order->save();
    }

    public function testSaveFailsUponClientHavingDifferentBusinessId()
    {
        $user = SaasUser::factory()->create();

        $business = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);
        $client = Client::factory()->withUser($user2)->create();

        $order = new Order();
        $order->business_id = $user->business_id;
        $order->saas_user_id = $user->id;
        $order->client_id = $client->id;

        // If static::saving upon model throws whatever exception laravel throws an \Error exception instead.
        $this->expectException(\Error::class);
        $order->save();
    }

    public function testOrderUpdateFailsUponUserIdChangeOnNotSaveBusiness()
    {
        $order = Order::factory()->create();
        $business = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);

        $order->saas_user_id = $user2->id;
        // If static::saving upon model throws whatever exception laravel throws an \Error exception instead.
        $this->expectException(\Error::class);
        $order->save();
    }

    public function testOrderUpdateFailsUponClientIdChangeOnNotSaveBusiness()
    {
        $order = Order::factory()->create();

        $business = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);
        $client = Client::factory()->withUser($user2)->create();

        $order->client_id = $client->id;
        // If static::saving upon model throws whatever exception laravel throws an \Error exception instead.
        $this->expectException(\Error::class);
        $order->save();
    }

    public function testOrderUpdateFailsUponBusinessIdChangeOnSave()
    {
        $order = Order::factory()->create();
        $business = Business::factory()->create();

        $order->business_id = $business->id;
        // If static::saving upon model throws whatever exception laravel throws an \Error exception instead.
        $this->expectException(\Error::class);
        $order->save();
    }

    public function testOrderUpdateSuceedsUponClientIdChangeOnSave()
    {
        $order = Order::factory()->create();

        $business = Business::find($order->business_id);
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);
        $client = Client::factory()->withUser($user2)->create();

        $order->client_id = $client->id;

        $order->save();
        $orderInDB = Order::find($order->id);


        $this->assertEquals($order->business_id , $orderInDB->business_id);
        $this->assertEquals($user2->business_id , $orderInDB->business_id);
        $this->assertEquals($client->business_id , $orderInDB->business_id);
        $this->assertEquals($business->id , $orderInDB->business_id);

        $this->assertEquals($client->id , $orderInDB->client_id);
    }

    public function testOrderUpdateSuceedsUponUserIdChangeOnSave()
    {
        $order = Order::factory()->create();
        $business = Business::find($order->business_id);
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);

        $order->saas_user_id = $user2->id;
        $order->save();

        $orderInDB = Order::find($order->id);

        $this->assertEquals($user2->business_id , $orderInDB->business_id);
        $this->assertEquals($business->id , $orderInDB->business_id);
        $this->assertEquals($order->client_id , $orderInDB->client_id);

        $client = Client::find($orderInDB->client_id);
        $this->assertEquals($client->business_id , $orderInDB->business_id);

    }
}

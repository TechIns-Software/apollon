<?php

namespace Feature\Controllers\API;

use App\Models\Business;
use App\Models\Delivery;
use App\Models\DeliveryOrder;
use App\Models\SaasUser;
use App\Models\Client;
use App\Models\Order;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
class ClientControllerTest extends TestCase
{

    use RefreshDatabase;
    public function testInsert()
    {
        $user = SaasUser::factory()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'name'=>'lalala',
            'surname'=>'lalala',
            'telephone'=>"6940000000",
            'phone1'=>"6940000000",
            'phone2'=>"6940000000",
            'state'=>"Αττική",
            'region'=>"Αθήνα",
            "description"=>"Ηαηαηα",
            "email"=>"user@example.com",
            "nomos"=>"Αττική",
            "afm"=>"1234",
            'stars'=>3
        ];

        $result = $this->post(route('client.create'),$payload);
        $jsonResult = $result->json();
        $result->assertStatus(201);
        $result->assertJson($payload);
        $itemInDb = Client::find($jsonResult['id']);
        $this->assertNotEmpty($itemInDb);

        $this->assertEquals($user->business_id,$itemInDb->business_id);
        $this->assertEquals($user->id,$itemInDb->saas_user_id);

        foreach ($payload as $key => $value) {
           $this->assertEquals($value, $itemInDb->$key);
        }
    }

    public function testInsertEmail()
    {
        $user = SaasUser::factory()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'name'=>'lalala',
            'surname'=>'lalala',
            'telephone'=>"6940000000",
            'phone1'=>"6940000000",
            'phone2'=>"6940000000",
            'state'=>"Αττική",
            'region'=>"Αθήνα",
            "description"=>"Ηαηαηα",
            "email"=>"dsaddsdsdsa",
            "nomos"=>"Αττική",
            'stars'=>3
        ];

        $result = $this->post(route('client.create'),$payload);

        $result->assertStatus(400);

        $clients = Client::count();
        $this->assertEquals(0,$clients);
    }

    public function testInsertCoords()
    {
        $user = SaasUser::factory()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'name'=>'lalala',
            'surname'=>'lalala',
            'telephone'=>"6940000000",
            'phone1'=>"6940000000",
            'phone2'=>"6940000000",
            'state'=>"Αττική",
            'region'=>"Αθήνα",
            "description"=>"Ηαηαηα",
            "longitude"=>"12.5",
            "latitude"=>"12.5",
        ];

        $result = $this->post(route('client.create'),$payload);
        $jsonResult = $result->json();

        $result->assertStatus(201);
        $result->assertJson($payload);
        $itemInDb = Client::find($jsonResult['id']);
        $this->assertNotEmpty($itemInDb);

        $this->assertEquals($user->business_id,$itemInDb->business_id);
        $this->assertEquals($user->id,$itemInDb->saas_user_id);

        foreach ($payload as $key => $value) {
            $this->assertEquals($value, $itemInDb->$key);
        }

    }

    public function testMissingLongitude()
    {
        $user = SaasUser::factory()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'name'=>'lalala',
            'surname'=>'lalala',
            'telephone'=>"6940000000",
            'phone1'=>"6940000000",
            'phone2'=>"6940000000",
            'state'=>"Αττική",
            'region'=>"Αθήνα",
            "description"=>"Ηαηαηα",
            "latitude"=>"12.5",
        ];

        $result = $this->post(route('client.create'),$payload);
        $result->assertStatus(400);

        $clients = Client::count();
        $this->assertEquals(0,$clients);
    }

    public function testMissingLatitude()
    {
        $user = SaasUser::factory()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'name'=>'lalala',
            'surname'=>'lalala',
            'telephone'=>"6940000000",
            'phone1'=>"6940000000",
            'phone2'=>"6940000000",
            'state'=>"Αττική",
            'region'=>"Αθήνα",
            "description"=>"Ηαηαηα",
            "latitude"=>"12.5",
        ];

        $result = $this->post(route('client.create'),$payload);
        $result->assertStatus(400);

        $clients = Client::count();
        $this->assertEquals(0,$clients);
    }

    public function testGetUserSuccess()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create();
        $customer->refresh();
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $result = $this->get("/api/client/".$customer->id);

        $result->assertStatus(200);
        $json = $result->json();

        $this->assertEquals($json['changes_count'],$customer->changes_count);
        foreach ($json as $key => $value) {
            if($key == 'created_at' || $key == 'updated_at' || $key=='changes_count'){
                continue;
            }
            $this->assertEquals($customer->$key,$value);
        }
    }


    public function testGetUserSuccessForUnauthorizedUser()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create();

        $business = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);

        Sanctum::actingAs(
            $user2,
            ['mobile_api']
        );

        $result = $this->get("/api/client/".$customer->id);

        $result->assertStatus(403);
    }


    public function testUpdateForUnAuthorizedUser()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create();

        $business = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);

        Sanctum::actingAs(
            $user2,
            ['mobile_api']
        );

        $payload=[
            'name'=>'lalala',
            'surname'=>'lalala',
            'telephone'=>"6940000000",
            'phone1'=>"6940000000",
            'phone2'=>"6940000000",
            'state'=>"Αττική",
            'region'=>"Αθήνα",
            "description"=>"Ηαηαηα",
            "longitude"=>"12.5",
            "latitude"=>"12.5",
            'stars'=>3.5
        ];

        $result = $this->post("/api/client/".$customer->id,$payload);

        $result->assertStatus(403);
    }

    public function testUpdateSuccess()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create()->refresh();
        $origCount = $customer->changes_count;
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'name'=>'lalala',
            'surname'=>'lalala',
            'telephone'=>"6940000000",
            'phone1'=>"6940000000",
            'phone2'=>"6940000000",
            'state'=>"Αττική",
            'region'=>"Αθήνα",
            "description"=>"Ηαηαηα",
            "email"=>"user@example.com",
            "nomos"=>"Attica",
            "afm"=>'1234',
            'stars'=>3
        ];

        $result = $this->post("/api/client/".$customer->id,$payload);

        $result->assertStatus(200);
        $json = $result->json();

        $customer = Client::find($customer->id);

        $this->assertGreaterThan($origCount,$customer->changes_count);

        foreach ($payload as $key => $value) {
            if($key == 'created_at' || $key == 'updated_at' || $key=='changes_count'){
                continue;
            }
            $this->assertEquals($value,$customer->$key);
            $this->assertEquals($value,$json[$key]);
        }
    }

    public function testUpdateWrongEmail()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create(['email'=>"user@example.com"])->refresh();
        $origCount = $customer->changes_count;
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=['email'=>"dsasaddsadassad"];

        $result = $this->post("/api/client/".$customer->id,$payload);
        $result->assertStatus(400);

        $customer = Client::find($customer->id);

        $this->assertEquals('user@example.com',$customer->email);

    }

    public function testUpdateEmailOnlySuccess()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create(['email'=>"user@example.com"])->refresh();
        $origCount = $customer->changes_count;
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=['email'=>"user12@example.com"];

        $result = $this->post("/api/client/".$customer->id,$payload);
        $result->assertStatus(200);

        $customer = Client::find($customer->id);

        $this->assertEquals('user12@example.com',$customer->email);

    }
    public function testUpdateCoordinatesSuccess()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create()->refresh();
        $origCount = $customer->changes_count;
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'name'=>'lalala',
            'surname'=>'lalala',
            'telephone'=>"6940000000",
            'phone1'=>"6940000000",
            'phone2'=>"6940000000",
            'state'=>"Αττική",
            'region'=>"Αθήνα",
            "description"=>"Ηαηαηα",
            "longitude"=>"12.5",
            "latitude"=>"12.5",
        ];

        $result = $this->post("/api/client/".$customer->id,$payload);

        $result->assertStatus(200);
        $json = $result->json();

        $customer = Client::find($customer->id);

        $this->assertGreaterThan($origCount,$customer->changes_count);

        foreach ($payload as $key => $value) {
            if($key == 'created_at' || $key == 'updated_at' || $key=='changes_count'){
                continue;
            }
            $this->assertEquals($value,$customer->$key);
            $this->assertEquals($value,$json[$key]);
        }

        $this->assertEquals("12.5",$customer->longitude);
        $this->assertEquals("12.5",$customer->latitude);

    }

    public function testUpdateCoordinatesMissingLongitude()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create(["longitude"=>"10.0","latitude"=>"10.0"])->refresh();
        $origCount = $customer->changes_count;
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'name'=>'lalala',
            'surname'=>'lalala',
            'telephone'=>"6940000000",
            'phone1'=>"6940000000",
            'phone2'=>"6940000000",
            'state'=>"Αττική",
            'region'=>"Αθήνα",
            "description"=>"Ηαηαηα",
            "latitude"=>"12.5",
        ];

        $result = $this->post("/api/client/".$customer->id,$payload);

        $result->assertStatus(400);

        $customer = Client::find($customer->id);

        $this->assertEquals("10.0",$customer->longitude);
        $this->assertEquals("10.0",$customer->latitude);
    }

    public function testUpdateCoordinatesMissingLatitude()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create(["longitude"=>"10.0","latitude"=>"10.0"])->refresh();
        $origCount = $customer->changes_count;
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'name'=>'lalala',
            'surname'=>'lalala',
            'telephone'=>"6940000000",
            'phone1'=>"6940000000",
            'phone2'=>"6940000000",
            'state'=>"Αττική",
            'region'=>"Αθήνα",
            "description"=>"Ηαηαηα",
            "longitude"=>"12.5",
        ];

        $result = $this->post("/api/client/".$customer->id,$payload);

        $result->assertStatus(400);

        $customer = Client::find($customer->id);

        $this->assertEquals("10.0",$customer->longitude);
        $this->assertEquals("10.0",$customer->latitude);
    }

    public function testDeleteUserForUnauthorizedUser()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create();

        $business = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);

        Sanctum::actingAs(
            $user2,
            ['mobile_api']
        );

        $result = $this->delete("/api/client/".$customer->id);

        $result->assertStatus(403);
    }

    public function testDeleteUserSuccess()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create();
        $id = $customer->id;
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $result = $this->delete("/api/client/".$customer->id);
        $result->assertStatus(200);

        // Aserting that
        $record = DB::table("client")->where('id',$id)->first();
        $this->assertNotEmpty($record);
        $this->assertNotEmpty($record->deleted_at);

        $client = Client::find($id);

        $this->assertEmpty($client);
    }

    public static function missingInputs()
    {
        return [
            [[
                'surname'=>'lalala',
                'telephone'=>"6940000000",
                'phone1'=>"6940000000",
                'phone2'=>"6940000000",
                'state'=>"Αττική",
                'region'=>"Αθήνα",
                "description"=>"Ηαηαηα",
            ]],
            [[
                'name'=>'lalala',
                'telephone'=>"6940000000",
                'phone1'=>"6940000000",
                'phone2'=>"6940000000",
                'state'=>"Αττική",
                'region'=>"Αθήνα",
                "description"=>"Ηαηαηα",
            ]],
            [[
                'telephone'=>"6940000000",
                'phone1'=>"6940000000",
                'phone2'=>"6940000000",
                'state'=>"Αττική",
                'region'=>"Αθήνα",
                "description"=>"Ηαηαηα",
            ]],
        ];
    }

    /**
     * @dataProvider missingInputs
     */
    public function testΙnsertFailsEmptyInput($payload)
    {
        $user = SaasUser::factory()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $result = $this->post(route('client.create'),$payload);

        $result->assertStatus(400);

        // Because we soft delete the clients we
        $clients = DB::table('client')->count();
        $this->assertEmpty($clients);
    }

    public function testΙnsertFailsNoInput()
    {
        $user = SaasUser::factory()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $result = $this->post(route('client.create'),[]);

        $result->assertStatus(400);

        // Because we soft delete the clients we
        $clients = DB::table('client')->count();
        $this->assertEmpty($clients);
    }

    public function testEditFailsNoInput()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $result = $this->post("/api/client/".$customer->id,[]);

        $result->assertStatus(400);

        // Because we soft delete the clients we
        $clientInDb = DB::table('client')->where('id',$customer->id)->first();
        $clientInDb = json_decode(json_encode($clientInDb),TRUE);

        $customer->refresh();

        foreach ($clientInDb as  $key=>$item) {
            $this->assertEquals($item,$customer->$key);
        }
    }

    public function testGetOrdersForASpecificClient()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->withOrders()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $result = $this->get("/api/client/".$customer->id."/orders",[]);

        $result->assertStatus(200);

        $expectedOrdersIds = Order::where('client_id',$customer->id)->get()->pluck('id');
        $resultOrders = $result->json('data');

        foreach ($resultOrders as $order){
            $this->assertContains($order['id'],$expectedOrdersIds);
        }
    }

    public function testGetOrdersWrongUser()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->withOrders()->create();

        $business = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business->id]);

        Sanctum::actingAs(
            $user2,
            ['mobile_api']
        );

        $result = $this->get("/api/client/".$customer->id."/orders",[]);

        $result->assertStatus(403);
    }

    public function testGetOrdersUserBelongsIntoSameBusiness()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->withOrders()->create();

        $user2 = SaasUser::factory()->create(['business_id'=>$user->business_id]);

        Sanctum::actingAs(
            $user2,
            ['mobile_api']
        );


        $result = $this->get("/api/client/".$customer->id."/orders",[]);

        $result->assertStatus(200);

        $expectedOrdersIds = Order::where('client_id',$customer->id)->get()->pluck('id');
        $resultOrders = $result->json('data');

        foreach ($resultOrders as $order){
            $this->assertContains($order['id'],$expectedOrdersIds);
        }
    }

    public function testGetOrdersMissingUser()
    {
        $user = SaasUser::factory()->create();
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        DB::statement("DELETE FROM client;");

        // Fetch Client id 445. Clients is an empty table
        $result = $this->get("/api/client/445/orders",[]);
        $result->assertStatus(404);
    }

    public function testGetOrdersDateRange()
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create();

        $date_from = Carbon::now()->modify("-10 days");
        $date_to = Carbon::now()->modify("+10 days");
        $date_off = Carbon::now()->modify("+20 days");

        for($date = (new Carbon($date_from));$date->lessThanOrEqualTo($date_off);$date->modify("+1 day")){
            Order::factory()->withUser($user)->create([
                'created_at'=>$date,
                'client_id'=>$customer->id,
                'business_id'=>$customer->business_id
            ]);
        }

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $result = $this->get("/api/client/".$customer->id."/orders?from_date=".$date_from->format('Y-m-d')."&to_date=".$date_to->format("Y-m-d"), []);
        $result->assertStatus(200);

        $expectedOrdersIds = Order::where('client_id',$customer->id)
            ->where('business_id',$customer->business_id)
            ->where('created_at',">=",$date_from)
            ->where('created_at',"<=",$date_to)
            ->orderBy('created_at','DESC');


        $expectedOrdersIds=$expectedOrdersIds->pluck('id');


        $unexpectedOrdersIds = Order::where('created_at',">",$date_to)->where('client_id',$customer->id)
            ->where('business_id',$customer->business_id)
            ->where('created_at',"<=",$date_off)
            ->orderBy('created_at','DESC')
            ->pluck('id');

        $resultOrders = $result->json('data');

        foreach ($resultOrders as $order){
            $this->assertContains($order['id'],$expectedOrdersIds);
            $this->assertNotContains($order['id'],$unexpectedOrdersIds);
        }
    }

    public static function wrongStarValues()
    {
        return [
            [-1],
            [10]
        ];
    }

    /**
     * @dataProvider wrongStarValues
     * @param $stars The number of Stars
     * @return void
     */
    public function testInsertStartsWrongValue(int $stars)
    {
        $user = SaasUser::factory()->create();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $payload=[
            'name'=>'lalala',
            'surname'=>'lalala',
            'telephone'=>"6940000000",
            'phone1'=>"6940000000",
            'phone2'=>"6940000000",
            'state'=>"Αττική",
            'region'=>"Αθήνα",
            "description"=>"Ηαηαηα",
            "email"=>"user@example.com",
            "nomos"=>"Attica",
            "afm"=>'1234',
            'stars'=>$stars
        ];

        $result = $this->post(route('client.create'),$payload);

        $result->assertStatus(400);

        // Because we soft delete the clients we
        $clients = DB::table('client')->count();
        $this->assertEmpty($clients);

    }

    /**
     * @dataProvider wrongStarValues
     * @param $stars The number of Stars
     * @return void
     */
    public function testUpdateStarsWrongValue(int $stars)
    {
        $user = SaasUser::factory()->create();
        $customer = Client::factory()->withUser($user)->create(['stars'=>3]);

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $result = $this->post("/api/client/".$customer->id,[]);
        $result->assertStatus(400);

        $clientInDb = Client::find($customer->id);

        $this->assertEquals(3,(int)$clientInDb->stars);
        $this->assertNotEquals($stars,(int)$clientInDb->stars);
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
    public function testInvalid($params)
    {
        $user = SaasUser::factory()->create();
        Client::factory(2)->withUser($user)->create(['business_id'=>$user->business_id]);

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $result = $this->get('/api/client?'.http_build_query($params));
        $result->assertStatus(400);
    }

    public function testOrderingClientName()
    {
        $user = SaasUser::factory()->create();
        $expectedNameSequence = [
            [
                'name' => 'Αναξαγόρας',
                'surname' => 'Αυξεντίου',
            ],
            [
                'name' => 'Αναξαγόρας',
                'surname' => 'Βουλγατάς',
            ],
            [
                'name' => 'Θεόδωρος',
                'surname' => 'Μαριάνου',
            ],
        ];

        $insertOrder = $expectedNameSequence;
        shuffle($insertOrder);
        foreach ($insertOrder as $nameSequence) {
            Client::factory()->withUser($user)->create(['name'=>$nameSequence['name'],'surname'=>$nameSequence['surname']]);
        }

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );
        $result = $this->get('/api/client?order_by=name&ordering=asc');
        $result->assertStatus(200);
        $data = $result->json('data');

        $names =  array_map(fn ($item) => ['name'=>$item['name'],'surname'=>$item['surname']], $data);

        $this->assertEquals($expectedNameSequence, $names);
    }

    public function testOrderingClientNameDesc()
    {
        $user = SaasUser::factory()->create();
        $expectedNameSequence = [
            [
                'name' => 'Αναξαγόρας',
                'surname' => 'Αυξεντίου',
            ],
            [
                'name' => 'Αναξαγόρας',
                'surname' => 'Βουλγατάς',
            ],
            [
                'name' => 'Θεόδωρος',
                'surname' => 'Μαριάνου',
            ],
        ];

        $expectedNameSequence = array_reverse($expectedNameSequence);

        $insertOrder = $expectedNameSequence;
        shuffle($insertOrder);
        foreach ($insertOrder as $nameSequence) {
            Client::factory()->withUser($user)->create(['name'=>$nameSequence['name'],'surname'=>$nameSequence['surname']]);
        }

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );
        $result = $this->get('/api/client?order_by=name&ordering=desc');
        $result->assertStatus(200);
        $data = $result->json('data');

        $names =  array_map(fn ($item) => ['name'=>$item['name'],'surname'=>$item['surname']], $data);

        $this->assertEquals($expectedNameSequence, $names);
    }

    public function testOrderingClientRegion()
    {
        $user = SaasUser::factory()->create();
        $regionOrdering = [
            [
                'nomos'=>'ΑΤΤΙΚΗΣ',
                'region'=>'ΑΧΑΡΝΕΣ'
            ],

            // Area or ordered first then nomos.
            [
                'nomos'=>'Λασιθίου',
                'region'=>'Μάταλα'
            ],
            [
                'nomos'=>'Ηλίας',
                'region'=>'Ολυμπία'
            ],
            [
                'nomos'=>'Ηλίας',
                'region'=>'Πύργος'
            ],
        ];

        $insertOrder = $regionOrdering;
        shuffle($insertOrder);
        foreach ($insertOrder as $sequence) {
            Client::factory()->withUser($user)->create(['nomos'=>$sequence['nomos'],'region'=>$sequence['region']]);
        }

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );
        $result = $this->get('/api/client?order_by=area&ordering=asc');
        $result->assertStatus(200);
        $data = $result->json('data');
        $regions =  array_map(fn ($item) => ['nomos'=>$item['nomos'],'region'=>$item['region']], $data);

        $this->assertEquals($regionOrdering, $regions);
    }

    public function testOrderingClientRegionDesc()
    {
        $user = SaasUser::factory()->create();
        $regionOrdering = [
            [
                'nomos'=>'ΑΤΤΙΚΗΣ',
                'region'=>'ΑΧΑΡΝΕΣ'
            ],

            // Area or ordered first then nomos.
            [
                'nomos'=>'Λασιθίου',
                'region'=>'Μάταλα'
            ],
            [
                'nomos'=>'Ηλίας',
                'region'=>'Ολυμπία'
            ],
            [
                'nomos'=>'Ηλίας',
                'region'=>'Πύργος'
            ],
        ];

        $insertOrder = $regionOrdering;
        shuffle($insertOrder);

        $regionOrdering = array_reverse($regionOrdering);

        foreach ($insertOrder as $sequence) {
            Client::factory()->withUser($user)->create(['nomos'=>$sequence['nomos'],'region'=>$sequence['region']]);
        }

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );
        $result = $this->get('/api/client?order_by=area&ordering=desc');
        $result->assertStatus(200);
        $data = $result->json('data');
        $regions =  array_map(fn ($item) => ['nomos'=>$item['nomos'],'region'=>$item['region']], $data);

        $this->assertEquals($regionOrdering, $regions);
    }

    public function testSearchDoesNotFetchClientFromOtherBusiness()
    {
        $business1 = Business::factory()->create();
        $user = SaasUser::factory()->create(['business_id'=>$business1->id]);
        $name="Alice";

        $notFoundClients = Client::factory()->create(['name'=>$name,'business_id'=>$user->business_id,'saas_user_id'=>$user->id]);
        $notFoundids = $notFoundClients->pluck('id')->toArray();

        $business2 = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business2->id]);
        $foundClients = Client::factory()->create(['name'=>$name,'business_id'=>$user->business_id,'saas_user_id'=>$user2->id]);
        $foundids = $foundClients->pluck('id')->toArray();

        Sanctum::actingAs(
            $user2,
            ['mobile_api']
        );

        $result = $this->get('/api/client?searchTerm='.$name);
        $result->assertStatus(200);

        $data = $result->json('data');
        foreach ($data as $item){
            $this->assertEquals($business2->id,$item['business_id']);
            $this->assertContains($item['id'],$foundids);
            $this->assertNotContains($item['id'],$notFoundids);
        }
    }

    public function testSearchDoesNotFetchClientFromOtherBusiness2()
    {
        $business1 = Business::factory()->create();
        $user = SaasUser::factory()->create(['business_id'=>$business1->id]);
        $name="Alice";

        $notFoundClients = Client::factory()->create(['name'=>$name,'business_id'=>$user->business_id,'saas_user_id'=>$user->id]);
        $notFoundids = $notFoundClients->pluck('id')->toArray();

        $notFoundClientsSameBusiness = Client::factory()->create(['name'=>'Babis','business_id'=>$user->business_id,'saas_user_id'=>$user->id]);
        $notFoundClientsSameBusinessIds = $notFoundClientsSameBusiness->pluck('id')->toArray();

        $business2 = Business::factory()->create();
        $user2 = SaasUser::factory()->create(['business_id'=>$business2->id]);
        $foundClients = Client::factory()->create(['name'=>$name,'business_id'=>$user->business_id,'saas_user_id'=>$user2->id]);
        $foundids = $foundClients->pluck('id')->toArray();

        Sanctum::actingAs(
            $user2,
            ['mobile_api']
        );

        $result = $this->get('/api/client?searchTerm='.$name);
        $result->assertStatus(200);

        $data = $result->json('data');
        foreach ($data as $item){
            $this->assertEquals($business2->id,$item['business_id']);
            $this->assertContains($item['id'],$foundids);
            $this->assertNotContains($item['id'],$notFoundids);
            $this->assertContains($item['id'],$notFoundClientsSameBusinessIds);
            $this->assertEquals($name,$item['name']);
            $this->assertNotEquals("Babis",$item['name']);
        }
    }
    public function testDelete()
    {
        $user = SaasUser::factory()->create();
        $client = Client::factory()->withUser($user)->withOrders()->create();

        $orders = Order::factory(5)->withUser($user)->create(['client_id'=>$client->id]);
        $orderIds = $orders->pluck('id')->toArray();

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $result = $this->delete('/api/client/'.$client->id);
        $result->assertStatus(200);

        $clientInDB = Client::find($client->id);
        $this->assertEmpty($clientInDB);

        // Initially orders were soft-deleted upon client deletion as well. Thus I needed to access db diretly bypassing the eloquent.
        $qb = DB::query()->from(Order::TABLE)
            ->whereIn('id',$orderIds)
            ->where('client_id',$client->id);

        $results = $qb->get();
        $this->assertEmpty($results);
    }

    public function testDeleteWithDelivery()
    {
        $user = SaasUser::factory()->create();
        $client = Client::factory()->withUser($user)->withOrders()->create();

        $orders = Order::factory(5)->withUser($user)->create(['client_id'=>$client->id]);
        $delivery = Delivery::factory()->create(['business_id'=>$user->business_id]);
        $orderIds=[];
        foreach ($orders as $order){
            DeliveryOrder::insert([['delivery_id'=>$delivery->id,'order_id'=>$order->id,'delivery_sequence'=>1]]);
            $orderIds[] = $order->id;
        }

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );
        $result = $this->delete('/api/client/'.$client->id);
        $result->assertStatus(200);

        $clientInDB = Client::find($client->id);
        $this->assertEmpty($clientInDB);


        // Using Sql because code snipet already existed.
        $qb = DB::query()->from(Order::TABLE)
            ->whereIn('id',$orderIds)
            ->where('client_id',$client->id);

        $results = $qb->get();
        $this->assertEmpty($results);

        $delivertOrder = DeliveryOrder::whereIn('order_id',$orderIds)->where('delivery_id',$delivery->id)->count();
        $this->assertEmpty($delivertOrder);
    }
}

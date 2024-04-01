<?php

namespace Feature\Controllers\API;

use App\Models\Business;
use App\Models\SaasUser;
use App\Models\Client;

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

        dump(Business::find($user->business_id)->is_active);

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
            "map_link"=>"https://www.google.com/maps/place/%CE%95%CE%BA%CE%BA%CE%BB%CE%B7%CF%83%CE%AF%CE%B1+%CE%91%CE%B3%CE%AF%CE%B1+%CE%A4%CF%81%CE%B9%CE%AC%CE%B4%CE%B1+%CE%BF%CE%B9%CE%BA%CE%BF%CE%B4%CE%BF%CE%BC%CE%B9%CE%BA%CE%BF+%CF%84%CE%B5%CF%84%CF%81%CE%B1%CE%B3%CF%89%CE%BD%CE%BF+%CE%9D0+300/@38.2029719,23.8062457,14z/data=!4m6!3m5!1s0x14a17480b334a967:0x194a13601500a784!8m2!3d38.2108136!4d23.8098944!16s%2Fg%2F1262hqdt7?entry=ttu"
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
            "map_link"=>"https://www.google.com/maps/place/%CE%95%CE%BA%CE%BA%CE%BB%CE%B7%CF%83%CE%AF%CE%B1+%CE%91%CE%B3%CE%AF%CE%B1+%CE%A4%CF%81%CE%B9%CE%AC%CE%B4%CE%B1+%CE%BF%CE%B9%CE%BA%CE%BF%CE%B4%CE%BF%CE%BC%CE%B9%CE%BA%CE%BF+%CF%84%CE%B5%CF%84%CF%81%CE%B1%CE%B3%CF%89%CE%BD%CE%BF+%CE%9D0+300/@38.2029719,23.8062457,14z/data=!4m6!3m5!1s0x14a17480b334a967:0x194a13601500a784!8m2!3d38.2108136!4d23.8098944!16s%2Fg%2F1262hqdt7?entry=ttu"
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
            "map_link"=>"https://www.google.com/maps/place/%CE%95%CE%BA%CE%BA%CE%BB%CE%B7%CF%83%CE%AF%CE%B1+%CE%91%CE%B3%CE%AF%CE%B1+%CE%A4%CF%81%CE%B9%CE%AC%CE%B4%CE%B1+%CE%BF%CE%B9%CE%BA%CE%BF%CE%B4%CE%BF%CE%BC%CE%B9%CE%BA%CE%BF+%CF%84%CE%B5%CF%84%CF%81%CE%B1%CE%B3%CF%89%CE%BD%CE%BF+%CE%9D0+300/@38.2029719,23.8062457,14z/data=!4m6!3m5!1s0x14a17480b334a967:0x194a13601500a784!8m2!3d38.2108136!4d23.8098944!16s%2Fg%2F1262hqdt7?entry=ttu"
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
            [
            [
                'surname'=>'lalala',
                'telephone'=>"6940000000",
                'phone1'=>"6940000000",
                'phone2'=>"6940000000",
                'state'=>"Αττική",
                'region'=>"Αθήνα",
                "description"=>"Ηαηαηα",
                "map_link"=>"https://www.google.com/maps/place/%CE%95%CE%BA%CE%BA%CE%BB%CE%B7%CF%83%CE%AF%CE%B1+%CE%91%CE%B3%CE%AF%CE%B1+%CE%A4%CF%81%CE%B9%CE%AC%CE%B4%CE%B1+%CE%BF%CE%B9%CE%BA%CE%BF%CE%B4%CE%BF%CE%BC%CE%B9%CE%BA%CE%BF+%CF%84%CE%B5%CF%84%CF%81%CE%B1%CE%B3%CF%89%CE%BD%CE%BF+%CE%9D0+300/@38.2029719,23.8062457,14z/data=!4m6!3m5!1s0x14a17480b334a967:0x194a13601500a784!8m2!3d38.2108136!4d23.8098944!16s%2Fg%2F1262hqdt7?entry=ttu"
            ]],
            [
            [
                'name'=>'lalala',
                'telephone'=>"6940000000",
                'phone1'=>"6940000000",
                'phone2'=>"6940000000",
                'state'=>"Αττική",
                'region'=>"Αθήνα",
                "description"=>"Ηαηαηα",
                "map_link"=>"https://www.google.com/maps/place/%CE%95%CE%BA%CE%BA%CE%BB%CE%B7%CF%83%CE%AF%CE%B1+%CE%91%CE%B3%CE%AF%CE%B1+%CE%A4%CF%81%CE%B9%CE%AC%CE%B4%CE%B1+%CE%BF%CE%B9%CE%BA%CE%BF%CE%B4%CE%BF%CE%BC%CE%B9%CE%BA%CE%BF+%CF%84%CE%B5%CF%84%CF%81%CE%B1%CE%B3%CF%89%CE%BD%CE%BF+%CE%9D0+300/@38.2029719,23.8062457,14z/data=!4m6!3m5!1s0x14a17480b334a967:0x194a13601500a784!8m2!3d38.2108136!4d23.8098944!16s%2Fg%2F1262hqdt7?entry=ttu"
            ]],
            [[
                'telephone'=>"6940000000",
                'phone1'=>"6940000000",
                'phone2'=>"6940000000",
                'state'=>"Αττική",
                'region'=>"Αθήνα",
                "description"=>"Ηαηαηα",
                "map_link"=>"https://www.google.com/maps/place/%CE%95%CE%BA%CE%BA%CE%BB%CE%B7%CF%83%CE%AF%CE%B1+%CE%91%CE%B3%CE%AF%CE%B1+%CE%A4%CF%81%CE%B9%CE%AC%CE%B4%CE%B1+%CE%BF%CE%B9%CE%BA%CE%BF%CE%B4%CE%BF%CE%BC%CE%B9%CE%BA%CE%BF+%CF%84%CE%B5%CF%84%CF%81%CE%B1%CE%B3%CF%89%CE%BD%CE%BF+%CE%9D0+300/@38.2029719,23.8062457,14z/data=!4m6!3m5!1s0x14a17480b334a967:0x194a13601500a784!8m2!3d38.2108136!4d23.8098944!16s%2Fg%2F1262hqdt7?entry=ttu"
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
}

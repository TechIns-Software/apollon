<?php

namespace Feature\Controllers\API;

use App\Models\SaasUser;
use App\Models\Client;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
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
}

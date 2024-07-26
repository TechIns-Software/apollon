<?php

namespace Feature\Controllers\API;

use App\Models\Business;
use App\Models\Driver;
use App\Models\SaasUser;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DriverControllerTest extends TestCase
{
    public function testCreate()
    {
        $user = SaasUser::factory()->create();
        $original_Driver_count = Driver::whereBusinessId($user->business_id)->count();
        $expectedDriverCount = $original_Driver_count+1;

        $driver_name = "Ldssadasds";

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->post('/api/driver',['name'=>$driver_name]);

        $response->assertStatus(201);
        $response->assertJson([
            'driver_name' => $driver_name,
        ]);

        $id = $response->json('id');
        $driverInDb = Driver::find($id);

        $this->assertNotEmpty($driverInDb);
        $this->assertEquals($driver_name,$driverInDb->driver_name);
        $driver_count = Driver::whereBusinessId($user->business_id)->count();
        $this->assertEquals($expectedDriverCount,$driver_count);
    }

    public function testCreateMissingNameFails(){
        $user = SaasUser::factory()->create();
        $original_Driver_count = Driver::whereBusinessId($user->business_id)->count();
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->post('/api/driver',[]);

        $response->assertStatus(400);

        $drivers = Driver::whereBusinessId($user->business_id)->count();
        $this->assertEquals($original_Driver_count,$drivers);
    }

    public function testUpdateSuccess()
    {
        $user = SaasUser::factory()->create();
        $driver = Driver::factory()->withUser($user)->create(['driver_name'=>"Orig Driver"]);

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->post('/api/driver/'.$driver->id,[
            'name'=>'Ldssadasds',
        ]);

        $response->assertStatus(200);

        $response->assertJson(['id'=>$driver->id,'driver_name'=>"Ldssadasds"]);

        $driverInDb  = Driver::find($driver->id);

        $this->assertNotEquals("Orig Driver",$driverInDb->driver_name);
        $this->assertEquals("Ldssadasds",$driverInDb->driver_name);
    }

    public function testUpdateWrongBusinessIdFails()
    {
        $business1 = Business::factory()->create();
        $user = SaasUser::factory()->withBusiness($business1)->create();

        $business2= Business::factory()->create();
        $user2 = SaasUser::factory()->withBusiness($business2)->create();

        $driver = Driver::factory()->withUser($user2)->create(['driver_name'=>"Orig Driver"]);

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->post('/api/driver/'.$driver->id,[
            'name'=>'Ldssadasds',
        ]);

        $response->assertStatus(403);

        $driverInDb = Driver::find($driver->id);

        $this->assertEquals("Orig Driver",$driverInDb->driver_name);
        $this->assertNotEquals("Ldssadasds",$driverInDb->driver_name);
    }

    public function testUpdateMissignDriver()
    {
        $user = SaasUser::factory()->create();
        $driver = Driver::factory()->withUser($user)->create(['driver_name'=>"Orig Driver"]);

        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->post('/api/driver/4443',[
            'name'=>'Ldssadasds',
        ]);

        $response->assertStatus(404);

        $driverInDb = Driver::find($driver->id);

        $this->assertEquals("Orig Driver",$driverInDb->driver_name);
        $this->assertNotEquals("Ldssadasds",$driverInDb->driver_name);
    }
}

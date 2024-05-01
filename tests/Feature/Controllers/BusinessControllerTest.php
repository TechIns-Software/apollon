<?php

namespace Tests\Feature\Controllers;

use App\Models\Business;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

class BusinessControllerTest extends TestCase
{
    use RefreshDatabase;
    public function testSavedActiveFalse()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $date = Carbon::now()->modify('+1 year');

        $response = $this->session(['__token'=>'1234'])->post('/business',[
            '__token'=>'1234',
            'name'=>'LOREM IPSUM INC',
            'active'=>false,
            'expiration_date'=>$date,
            'vat_num'=>'125562123',
            'doy'=>'Αθηνών'
        ]);

        $jsonResponse = $response->json();
        $response->assertStatus(201);

        $businessInDB = Business::find($jsonResponse['id']);
        $this->assertNotEmpty($businessInDB);

        $this->assertEquals($jsonResponse['name'],$businessInDB['name']);
        $this->assertEquals('LOREM IPSUM INC',$businessInDB['name']);

        $this->assertEquals($jsonResponse['vat'],$businessInDB['vat']);
        $this->assertEquals('125562123',$businessInDB['vat']);

        $this->assertEquals($jsonResponse['doy'],$businessInDB['doy']);
        $this->assertEquals('Αθηνών',$businessInDB['doy']);

        $this->assertFalse(parseBool($jsonResponse['is_active']));
        $this->assertFalse($businessInDB->is_active);

        $this->assertEquals($date->format('Y-m-d'),$jsonResponse['expiration_date']);
        $this->assertEquals($date->format('Y-m-d'),$businessInDB->expiration_date);
    }

    public function testSavedActiveTrue()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $date = Carbon::now()->modify('+1 year');
        $response = $this->session(['__token'=>'1234'])->post('/business',[
            '__token'=>'1234',
            'name'=>'LOREM IPSUM INC',
            'active'=>true,
            'expiration_date'=>$date->format('Y-m-d'),
            'vat_num'=>'125562123',
            'doy'=>'Αθηνών'
        ]);

        $jsonResponse = $response->json();

        $response->assertStatus(201);

        $businessInDB = Business::find($jsonResponse['id']);
        $this->assertNotEmpty($businessInDB);

        $this->assertEquals($jsonResponse['name'],$businessInDB['name']);
        $this->assertEquals('LOREM IPSUM INC',$businessInDB['name']);

        $this->assertEquals($jsonResponse['vat'],$businessInDB['vat']);
        $this->assertEquals('125562123',$businessInDB['vat']);

        $this->assertEquals($jsonResponse['doy'],$businessInDB['doy']);
        $this->assertEquals('Αθηνών',$businessInDB['doy']);

        $this->assertTrue(parseBool($jsonResponse['is_active']));
        $this->assertTrue($businessInDB->is_active);

        $this->assertEquals($date->format('Y-m-d'),$jsonResponse['expiration_date']);
        $this->assertEquals($date->format('Y-m-d'),$businessInDB->expiration_date);
    }

    public function testSavedActiveOn()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $date = Carbon::now()->modify('+1 year');

        $response = $this->session(['__token'=>'1234'])->post('/business',[
            '__token'=>'1234',
            'name'=>'LOREM IPSUM INC',
            'active'=>'on',
            'expiration_date'=>$date,
            'vat_num'=>'125562123',
            'doy'=>'Αθηνών'
        ]);

        $jsonResponse = $response->json();
        $response->assertStatus(201);

        $businessInDB = Business::find($jsonResponse['id']);
        $this->assertNotEmpty($businessInDB);

        $this->assertEquals($jsonResponse['name'],$businessInDB['name']);
        $this->assertEquals('LOREM IPSUM INC',$businessInDB['name']);

        $this->assertEquals($jsonResponse['vat'],$businessInDB['vat']);
        $this->assertEquals('125562123',$businessInDB['vat']);

        $this->assertEquals($jsonResponse['doy'],$businessInDB['doy']);
        $this->assertEquals('Αθηνών',$businessInDB['doy']);

        $this->assertTrue(parseBool($jsonResponse['is_active']));
        $this->assertTrue($businessInDB->is_active);

        $this->assertEquals($date->format('Y-m-d'),$jsonResponse['expiration_date']);
        $this->assertEquals($date->format('Y-m-d'),$businessInDB->expiration_date);
    }

    public function testSavedActiveOff()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $date = Carbon::now()->modify('+1 year');

        $response = $this->session(['__token'=>'1234'])->post('/business',[
            '__token'=>'1234',
            'name'=>'LOREM IPSUM INC',
            'active'=>'off',
            'expiration_date'=>$date,
            'vat_num'=>'125562123',
            'doy'=>'Αθηνών'
        ]);

        $jsonResponse = $response->json();
        $response->assertStatus(201);

        $businessInDB = Business::find($jsonResponse['id']);
        $this->assertNotEmpty($businessInDB);

        $this->assertEquals($jsonResponse['name'],$businessInDB['name']);
        $this->assertEquals('LOREM IPSUM INC',$businessInDB['name']);

        $this->assertEquals($jsonResponse['vat'],$businessInDB['vat']);
        $this->assertEquals('125562123',$businessInDB['vat']);

        $this->assertEquals($jsonResponse['doy'],$businessInDB['doy']);
        $this->assertEquals('Αθηνών',$businessInDB['doy']);

        $this->assertFalse(parseBool($jsonResponse['is_active']));
        $this->assertFalse($businessInDB->is_active);

        $this->assertEquals($date->format('Y-m-d'),$jsonResponse['expiration_date']);
        $this->assertEquals($date->format('Y-m-d'),$businessInDB->expiration_date);
    }

    public function testSavedActiveInvalid()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->session(['__token'=>'1234'])->post('/business',[
            '__token'=>'1234',
            'name'=>'LOREM IPSUM INC',
            'active'=>'llalalala',
            'expiration_date'=>Carbon::now()->modify('+1 year'),
            'vat_num'=>'125562123',
            'doy'=>'Αθηνών'
        ]);

        $response->assertStatus(400);

        $business = Business::all();
        $this->assertEmpty($business->all());
    }

    public function testSavedActiveNoValue()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->session(['__token'=>'1234'])->post('/business',[
            '__token'=>'1234',
            'name'=>'LOREM IPSUM INC',
            'expiration_date'=>Carbon::now()->modify('+1 year'),
            'vat_num'=>'125562123',
            'doy'=>'Αθηνών'
        ]);


        $response->assertStatus(400);

        $business = Business::all();
        $this->assertEmpty($business->all());
    }

    public function testInsertInvalidExpirationDate()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->session(['__token'=>'1234'])->post('/business',[
            '__token'=>'1234',
            'name'=>'LOREM IPSUM INC',
            'active'=>false,
            'expiration_date'=>'dsadwadsa',
            'vat_num'=>'125562123',
            'doy'=>'Αθηνών'
        ]);


        $response->assertStatus(400);

        $business = Business::all();
        $this->assertEmpty($business->all());
    }

    public function testInsertInvalidExpirationDateAsString()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->session(['__token'=>'1234'])->post('/business',[
            '__token'=>'1234',
            'name'=>'LOREM IPSUM INC',
            'active'=>false,
            'expiration_date'=>'2024-01-12',
            'vat_num'=>'125562123',
            'doy'=>'Αθηνών'
        ]);


        $response->assertStatus(201);

        $jsonResponse = $response->json();

        $businessInDB = Business::find($jsonResponse['id']);
        $this->assertNotEmpty($businessInDB);

        $this->assertEquals($jsonResponse['name'],$businessInDB['name']);
        $this->assertEquals('LOREM IPSUM INC',$businessInDB['name']);

        $this->assertEquals($jsonResponse['vat'],$businessInDB['vat']);
        $this->assertEquals('125562123',$businessInDB['vat']);

        $this->assertEquals($jsonResponse['doy'],$businessInDB['doy']);
        $this->assertEquals('Αθηνών',$businessInDB['doy']);

        $this->assertFalse(parseBool($jsonResponse['is_active']));
        $this->assertFalse($businessInDB->is_active);

        $this->assertEquals('2024-01-12',$jsonResponse['expiration_date']);
        $this->assertEquals('2024-01-12',$businessInDB->expiration_date);
    }

    public function testEditSuccess()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();

        $this->actingAs($user);

        $response = $this->session(['__token'=>'1234'])->post('/business/edit',[
            'business_id'=>$business->id,
            '__token'=>'1234',
            'name'=>'LOREM IPSUM INC',
            'active'=>false,
            'expiration_date'=>'2024-01-12',
            'vat_num'=>'125562123',
            'doy'=>'Αθηνών'
        ]);


        $jsonResponse = $response->json();
        $response->assertStatus(200);

        $businessInDB = Business::find($business->id);
        $this->assertNotEmpty($businessInDB);

        $this->assertEquals($jsonResponse['name'],$businessInDB['name']);
        $this->assertEquals('LOREM IPSUM INC',$businessInDB['name']);

        $this->assertEquals($jsonResponse['vat'],$businessInDB['vat']);
        $this->assertEquals('125562123',$businessInDB['vat']);

        $this->assertEquals($jsonResponse['doy'],$businessInDB['doy']);
        $this->assertEquals('Αθηνών',$businessInDB['doy']);

        $this->assertFalse(parseBool($jsonResponse['is_active']));
        $this->assertFalse($businessInDB->is_active);

        $this->assertEquals('2024-01-12',$jsonResponse['expiration_date']);
        $this->assertEquals('2024-01-12',$businessInDB->expiration_date);
    }

    public function testEditNoData()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();

        $this->actingAs($user);

        $response = $this->session(['__token'=>'1234'])->post('/business/edit',[
            'business_id'=>$business->id,
        ]);

        $response->assertStatus(422);


        $businessInDB = Business::find($business->id);
        $this->assertNotEmpty($businessInDB);

        $this->assertEquals($businessInDB->name,$business->name);
        $this->assertEquals($businessInDB->vat,$business->vat);
        $this->assertEquals($businessInDB->doy,$business->doy);
        $this->assertEquals($businessInDB->is_active,$business->is_active);
        $this->assertEquals($businessInDB->expiration_date,$business->expiration_date);
    }

}

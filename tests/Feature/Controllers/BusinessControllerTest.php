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

        $response = $this->session(['__token'=>'1234'])->post('/business',[
            '__token'=>'1234',
            'name'=>'LOREM IPSUM INC',
            'active'=>false,
            'expiration_date'=>Carbon::now()->modify('+1 year'),
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
    }

    public function testSavedActiveTrue()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->session(['__token'=>'1234'])->post('/business',[
            '__token'=>'1234',
            'name'=>'LOREM IPSUM INC',
            'active'=>true,
            'expiration_date'=>Carbon::now()->modify('+1 year'),
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
    }

    public function testSavedActiveOn()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->session(['__token'=>'1234'])->post('/business',[
            '__token'=>'1234',
            'name'=>'LOREM IPSUM INC',
            'active'=>'on',
            'expiration_date'=>Carbon::now()->modify('+1 year'),
            'vat_num'=>'125562123',
            'doy'=>'Αθηνών'
        ]);

        $jsonResponse = $response->json();
        dump($jsonResponse);
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
    }

    public function testSavedActiveOff()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->session(['__token'=>'1234'])->post('/business',[
            '__token'=>'1234',
            'name'=>'LOREM IPSUM INC',
            'active'=>'off',
            'expiration_date'=>Carbon::now()->modify('+1 year'),
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
}

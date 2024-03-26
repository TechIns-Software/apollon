<?php

namespace Tests\Feature\Controllers\Panel;

use App\Models\Business;
use App\Models\SaasUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaasUserControllerTest extends TestCase
{

    use RefreshDatabase;

    public function testInsertSuccess()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $this->actingAs($user);

        $response = $this->session(['__token'=>'sadsadasdsa'])
            ->post(route('business.user.create'),[
                '__token'=>'sadsadasdsa',
                'name' =>"Lalala",
                'email'=>'user@example.com',
                'password'=>'1234',
                'password_confirmation'=>'1234',
                'business_id' => $business->id
            ]);

        $json = $response->json();
        $response->assertStatus(201);

        $this->assertNotEmpty($json['id']);

        $this->assertFalse(isset($json['password']));

        $this->assertEquals('Lalala',$json['name']);
        $this->assertEquals('user@example.com',$json['email']);

        $userInDb = SaasUser::find($json['id']);
        $this->assertNotEmpty($userInDb);

        $this->assertEquals('Lalala',$userInDb->name);
        $this->assertEquals('user@example.com',$userInDb->email);

        $this->assertTrue(password_verify('1234',$userInDb->password));

    }

    /**
     * I test the flow where once a SassUser created can login via API
     *
     * @depends testInsertSuccess
     */
    public function testInsertSuccessUserCanLoginViaApi()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $business = Business::factory()->create();

        $email = 'user@example.com';
        $password = '1234';
        $authBasicCredentials = base64_encode($email.":".$password);
        $response = $this->session(['__token'=>'sadsadasdsa'])
            ->post(route('business.user.create'),[
                '__token'=>'sadsadasdsa',
                'name' =>"Lalala",
                'email'=>$email,
                'password'=>$password,
                'password_confirmation'=>$password,
                'business_id' => $business->id
            ]);

        $response->assertStatus(201);

        $tokenResponse = $this->withHeaders([
            'Authorization' => 'Basic ' . $authBasicCredentials,
        ])->put(route('api.login'));

        $tokenResponse->assertStatus(201);
    }

}

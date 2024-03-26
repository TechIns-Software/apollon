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

    public static function missingInput()
    {
        $email = 'user@example.com';
        $password = '1234';

        $input = [
            '__token'=>'sadsadasdsa',
            'name' =>"Lalala",
            'email'=>$email,
            'password'=>$password,
            'password_confirmation'=>$password
        ];


        $keysToRemove = ['name','email','password','password_confirmation'];

        $inputCombinations = [];

        // Generate combinations of excluded keys
        foreach ($keysToRemove as $key) {
            $missingInput = $input;
            unset($missingInput[$key]);
            $inputCombinations[] = [
                $missingInput
            ];
        }

        return $inputCombinations;
    }

    public static function wrongInput()
    {
        return [
            [[
                '__token'=>'sadsadasdsa',
                'name' =>"Lalala",
                'email'=> 'user@example.com',
                'password'=>'1234',
                'password_confirmation'=>'12345'
            ]],
            [[
                '__token'=>'sadsadasdsa',
                'name' =>"Lalala",
                'email'=> 'user@example.com',
                'password'=>'12345',
                'password_confirmation'=>'1234'
            ]],
            [[
                '__token'=>'sadsadasdsa',
                'name' =>"Lalala",
                'email'=> 'hgahaha',
                'password'=>'1234',
                'password_confirmation'=>'1234'
            ]],
            [[
                '__token'=>'sadsadasdsa',
                'name' =>"Lalala",
                'email'=> 'hgahaha',
                'password'=>'12345',
                'password_confirmation'=>'1234'
            ]]
        ];
    }

    /**
     * @dataProvider missingInput
     * @dataProvider wrongInput
     */
    public function testError400UponInsertWithMissingInput(array $input)
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $business = Business::factory()->create();

        $input['business_id'] = $business->id;

        $response = $this->session(['__token'=> $input["__token"]])
            ->post(route('business.user.create'),$input);

        $response->assertStatus(400);

        $saasUser = SaasUser::where('business_id',$business->id)->exists();
        $this->assertFalse($saasUser);
    }


    /**
     * @dataProvider wrongInput
     */
    public function testEditWrongInput(array $input)
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $business = Business::factory()->create();
        $saasUser = SaasUser::factory()->create(['business_id'=>$business->id]);
        $input['user_id'] = $saasUser->id;
        $response = $this->session(['__token'=> $input["__token"]])
            ->post(route('business.user.edit'),$input);

        $response->assertStatus(400);

        $userInDB = SaasUser::find($saasUser->id);

        $this->assertEquals($saasUser->name,$userInDB->name);
        $this->assertEquals($saasUser->email,$userInDB->email);
        $this->assertEquals($saasUser->password,$userInDB->password);
    }

    public function testEditSuccess()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $business = Business::factory()->create();
        $saasUser = SaasUser::factory()->create(['business_id'=>$business->id]);

        $input = [
            '__token'=> '12234',
            'user_id'=>$saasUser->id,
            'email'=>'lslsls@example.com',
            'name'=>"hahahaha"
        ];

        $response = $this->session(['__token'=> '12234'])
            ->post(route('business.user.edit'),$input);

        $userInDB = SaasUser::find($saasUser->id);

        $this->assertEquals("hahahaha",$userInDB->name);
        $this->assertEquals('lslsls@example.com',$userInDB->email);
        $this->assertEquals($saasUser->password,$userInDB->password);
    }

    public function testEditSuccessPassword()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $business = Business::factory()->create();
        $saasUser = SaasUser::factory()->create(['business_id'=>$business->id]);

        $input = [
            '__token'=> '12234',
            'user_id'=>$saasUser->id,
            'password'=>'55555',
            'password_confirmation'=>"55555"
        ];

        $response = $this->session(['__token'=> '12234'])
            ->post(route('business.user.edit'),$input);

        $userInDB = SaasUser::find($saasUser->id);

        $this->assertEquals($saasUser->name,$userInDB->name);
        $this->assertEquals($saasUser->email,$userInDB->email);
        $this->assertTrue(password_verify('55555',$userInDB->password));
    }

    /**
     * @depends testEditSuccessPassword
     */
    public function testEditSuccessPasswordLogin()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $business = Business::factory()->create();
        $saasUser = SaasUser::factory()->create(['business_id'=>$business->id]);

        $input = [
            '__token'=> '12234',
            'user_id'=>$saasUser->id,
            'password'=>'55555',
            'password_confirmation'=>"55555"
        ];

        $response = $this->session(['__token'=> '12234'])
            ->post(route('business.user.edit'),$input);

        $userInDB = SaasUser::find($saasUser->id);

        $this->assertEquals($saasUser->name,$userInDB->name);
        $this->assertEquals($saasUser->email,$userInDB->email);
        $this->assertTrue(password_verify('55555',$userInDB->password));
    }
}

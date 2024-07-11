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
            ->post(route('business.user.create',['id'=>$business->id]),[
                '__token'=>'sadsadasdsa',
                'name' =>"Lalala",
                'email'=>'user@example.com',
                'password'=>'1234',
                'password_confirmation'=>'1234',
            ]);

        $response->assertStatus(201);

        $userInDb = SaasUser::whereBusinessId($business->id)
            ->whereEmail('user@example.com')
            ->orderBy('created_at','DESC')
            ->first();

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
            ->post(route('business.user.create',['id'=>$business->id]),[
                '__token'=>'sadsadasdsa',
                'name' =>"Lalala",
                'email'=>$email,
                'password'=>$password,
                'password_confirmation'=>$password
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
        ];


        $keysToRemove = ['name','email','password'];

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
                'email'=> 'hgahaha',
                'password'=>'1234',
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

        $response = $this->session(['__token'=> $input["__token"]])
            ->post(route('business.user.create',['id'=>$business->id]),$input);

        $response->assertStatus(400);

        $saasUser = SaasUser::where('business_id',$business->id)->exists();
        $this->assertFalse($saasUser);
    }

    public function testInsertFails400UponInsertingaSaasUserWithExistingEmail()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $business = Business::factory()->create();

        $saasUser = SaasUser::factory()->create(['business_id'=>$business->id]);

        $input = [
            '__token'=>'sadsadasdsa',
            'email'=>$saasUser->email,
            'password'=>'1234',
            'name'=>"Lalalalal"
        ];

        $response = $this->session(['__token'=> $input["__token"]])
            ->post(route('business.user.create',['id'=>$business->id]),$input);

        $response->assertStatus(400);

        $saasUserCount = SaasUser::whereBusinessId($business->id)->where('email',$saasUser->email)->count();
        $this->assertEquals(1, $saasUserCount);
    }

    public function testInsertUserSameEmailDifferentBusiness()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $business = Business::factory()->create();

        $business2 = Business::factory()->create();
        $saasUser = SaasUser::factory()->create(['business_id'=>$business2->id]);

        $input = [
            '__token'=>'sadsadasdsa',
            'email'=>$saasUser->email,
            'password'=>'1234',
            'name'=>"Lalalalal"
        ];

        $response = $this->session(['__token'=> $input["__token"]])
            ->post(route('business.user.create',['id'=>$business->id]),$input);
        $json = $response->json();
        $response->assertStatus(400);

        $saasUserCount = SaasUser::whereBusinessId($business->id)->where('email',$saasUser->email)->count();
        $this->assertEquals(0, $saasUserCount);

        $saasUserCount = SaasUser::whereBusinessId($business2->id)->where('email',$saasUser->email)->count();
        $this->assertEquals(1, $saasUserCount);
    }

    public function testEditWrongEmail()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $business = Business::factory()->create();
        $saasUser = SaasUser::factory()->create(['business_id'=>$business->id]);
        $input['user_id'] = $saasUser->id;

        $input = [
            '__token'=>'sadsadasdsa',
            'name' =>"Lalala",
            'email'=> 'hgahaha',
            'password'=>'1234',
        ];

        $response = $this->session(['__token'=> $input["__token"]])
            ->post(route('business.user.edit',['id'=>$saasUser->id]),$input);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);

        $userInDB = SaasUser::find($saasUser->id);

        $this->assertEquals($saasUser->name,$userInDB->name);
        $this->assertEquals($saasUser->email,$userInDB->email);
        $this->assertEquals($saasUser->password,$userInDB->password);
    }

    public function testUserUpdateSameEmailProvided()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $business = Business::factory()->create();
        $saasUser = SaasUser::factory()->create(['business_id'=>$business->id,'email'=>'lslsls@example.com']);

        $input = [
            '__token'=> '12234',
            'email'=>$saasUser->email,
            'name'=>"hahahaha",
            'password'=>"55555"
        ];

        $response = $this->session(['__token'=> '12234'])
            ->post(route('business.user.edit',['id'=>$saasUser->id]),$input);

        $response->assertStatus(302);

        $userInDB = SaasUser::find($saasUser->id);

        $this->assertEquals("hahahaha",$userInDB->name);
        $this->assertEquals('lslsls@example.com',$userInDB->email);
        $this->assertNotEquals($saasUser->password,$userInDB->password);
        $this->assertTrue(password_verify("55555",$userInDB->password));
    }

    public function testNoInputGivenFails()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $business = Business::factory()->create();
        $saasUser = SaasUser::factory()->create(['business_id'=>$business->id,'email'=>'lslsls@example.com']);

        $response = $this->session(['__token'=> '12234'])
            ->post(route('business.user.edit',['id'=>$saasUser->id]),[]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['msg'=>"Δεν δώθηκαν στοιχεία για αποθήκευση"]);

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
            'email'=>'lslsls@example.com',
            'name'=>"hahahaha"
        ];


        $response = $this->session(['__token'=> '12234'])
            ->post(route('business.user.edit',['id'=>$saasUser->id]),$input);

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
            'password'=>'55555',
            'password_confirmation'=>"55555"
        ];

        $response = $this->session(['__token'=> '12234'])
            ->post(route('business.user.edit',['id'=>$saasUser->id]),$input);

        $userInDB = SaasUser::find($saasUser->id);

        $this->assertEquals($saasUser->name,$userInDB->name);
        $this->assertEquals($saasUser->email,$userInDB->email);
        $this->assertTrue(password_verify('55555',$userInDB->password));
    }

    /**
     * @dataProvider missingInput
     */
    public function testUpdateMissingPart(array $input)
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $business = Business::factory()->create();
        $saasUser = SaasUser::factory()->create(['business_id'=>$business->id]);

        $allKeys = ['name','email','password'];

        $missingKeys=[];
        foreach ($allKeys as $key) {
            if(!isset($input[$key])){
                $missingKeys[] = $key;
            }
        }

        $this->session(['__token'=> $input['__token']])
            ->post(route('business.user.edit',['id'=>$saasUser->id]),$input);
        $userInDB = SaasUser::find($saasUser->id);

        foreach($missingKeys as $key){
            $this->assertEquals($saasUser->$key,$userInDB->$key);
        }

        foreach($input as $key=>$value){
            if($key=="__token"){
                continue;
            }
            if($key=="password"){
                $this->assertTrue(password_verify($value,$userInDB->$key));
                continue;
            }
            $this->assertEquals($value,$userInDB->$key);
        }
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
            'password'=>'55555',
            'password_confirmation'=>"55555"
        ];

        $response = $this->session(['__token'=> '12234'])
            ->post(route('business.user.edit',['id'=>$saasUser->id]),$input);

        $userInDB = SaasUser::find($saasUser->id);

        $this->assertEquals($saasUser->name,$userInDB->name);
        $this->assertEquals($saasUser->email,$userInDB->email);
        $this->assertTrue(password_verify('55555',$userInDB->password));
    }
}

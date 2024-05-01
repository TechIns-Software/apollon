<?php

namespace Feature\Controllers\API;

use App\Models\SaasUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SaasUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testPasswordUpdate()
    {
        $user = SaasUser::factory()->create();
        $user->password = Hash::make('1234');
        $user->save();
        Sanctum::actingAs(
            $user,
            ['mobile_api']
        );

        $response = $this->post('/api/user/password',[
           'password'=>'3456',
           'password_confirmation'=>'3456',
        ]);

        $response->assertStatus(201);

        $userInDB = SaasUser::find($user->id);

        $this->assertTrue(Hash::check('3456',$userInDB->password));
    }
}

<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\SaasUser;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaasUser>
 */
class SaasUserFactory extends UserFactory
{

    public function configure():static
    {
        return $this->afterMaking(function (SaasUser $user){
            if(empty($user->business_id)){
               return;
            }
            // I do not use Eloquent Model Because at time of writing the was not need for it.
            $id=DB::table('business')->inRandomOrder()->select('id')->first()->id;

            if(empty($id)){
               $id = Business::factory()->create()->id;
            }

            $user->business_id = $id;
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\SaasUser;
use Illuminate\Database\Seeder;
class SaasUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $business_id = env('BUSINESS_ID');
        $userNum = env('USER_NUM')??1;
        if($business_id){
            $user = SaasUser::factory($userNum)->create(['business_id' => $business_id]);
        }else {
            $user = SaasUser::factory($userNum)->create();
        }
    }
}

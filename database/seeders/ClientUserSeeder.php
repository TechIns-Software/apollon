<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Client;
use App\Models\SaasUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $business = Business::inRandomOrder()->limit(3)->pluck('id')->toArray();
        if(empty($business)){
            $business = Business::factory(3)->create()->pluck('id')->toArray();
        }

        $users=[];
        foreach ($business as $business_id){
           $users[]=SaasUser::factory()->create(['business_id'=>$business_id]);
        }

        foreach ($users as $user){
            Client::factory(500)->create(['saas_user_id'=>$user->id,'business_id'=>$user->business_id]);
        }
    }
}

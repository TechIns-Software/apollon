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
        $user = SaasUser::factory()->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Products extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $args=[];
        $business_id =env('BUSINESS_ID');
        if(!empty($business_id)){
            $args['business_id']=$business_id;
        }
        dump($args);
        Product::factory(200)->create($args);
    }
}

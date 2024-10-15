<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $args=[];
        $business_id = env('BUSINESS_ID');
        $productNum = env('PRODUCT_NUM',200);
        $namePrefix = env('NAME_PREFIX');
        if(!empty($business_id)){
            $args['business_id']=$business_id;
        }

        if(!empty($namePrefix)){
            for($i = $productNum; $i>0;$i--){
                $args['name']=$namePrefix." ".$i;
                Product::factory($productNum)->create($args);
            }
            return;
        }

        dump($args);
        Product::factory($productNum)->create($args);
    }
}

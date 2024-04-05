<?php

namespace Feature\Controllers\Panel;

use App\Models\Business;
use App\Models\Product;
use Tests\TestCase;
use App\Models\User;
class ProductControllersTest extends TestCase
{
    public function testAddProduct()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $this->actingAs($user);

        $body=[
            'name'=>"Hello",
            'business_id'=>$business->id
        ];

        $response = $this->post('/product',$body);
        $response->assertStatus(201);

        $json = $response->json();
        $this->assertEquals("Hello",$json['name']);

        $product = Product::find($json['id']);

        $this->assertEquals("Hello",$product->name);
        $this->assertEquals($business->id,$product->business_id);
    }

}

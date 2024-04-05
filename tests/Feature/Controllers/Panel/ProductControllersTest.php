<?php

namespace Feature\Controllers\Panel;

use App\Models\Business;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
class ProductControllersTest extends TestCase
{
    use RefreshDatabase;

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

    public function testUpdateProduct()
    {
        $user = User::factory()->create();
        $this->actingAs($user);


        $business = Business::factory()->create();
        $products = Product::factory()->count(3)->create(['business_id'=>$business->id]);
        $unModifiedProdusts = Product::factory()->count(3)->create(['business_id'=>$business->id,'name'=>'Unmodified']);

        $payload = [];
        foreach ($products as $product) {
            $payload[(int)$product->id]="LOrem Ipsum";
        }

        $response = $this->post('/product/edit',['products'=>$payload]);
        $json = $response->json();
        dump($json);
        $response->assertStatus(200);

        $responsedIds = array_map(function ($product) { return (int)$product['id'];},$json);
        sort($responsedIds);
        $expectedKeys = array_keys($payload);
        sort($expectedKeys);

        $this->assertEquals($expectedKeys,$responsedIds);

        foreach ($json as $product){
            $this->assertEquals("LOrem Ipsum",$product['name']);
            $productInDb = Product::find($product['id']);
            $this->assertNotEmpty($productInDb);
            $this->assertEquals("LOrem Ipsum",$productInDb->name);
        }

        foreach ($unModifiedProdusts as $product){
            $productInDb = Product::find($product->id);
            $this->assertEquals("Unmodified",$productInDb->name);
            $this->assertNotContains($product->id,$responsedIds);
            $this->assertNotContains($product->id,$expectedKeys);
        }

    }
}

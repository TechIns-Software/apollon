<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductsController extends Controller
{
    public function addProduct(Request $request)
    {
        $items = $request->all();

        $validator = Validator::make($items, [
            'name'=>'required',
            'business_id'=>'required|integer|min:1|exists:business,id'
        ]);

        if ($validator->fails()) {
            return new JsonResponse(['errors' => $validator->errors()], 400);
        }

        try {
            $product = new Product();
            $product->name = $items['name'];
            $product->business_id = $items['business_id'];
            $product->save();
        }catch (\Exception $e){
            report($e);
            return new JsonResponse(['msg'=>"Αδυναμία αποθήκευσης."], 500);
        }

        return new JsonResponse($product, 201);
    }

    public function listProducts(Request $request)
    {
        $items = $request->all();

        $input = [
            'business_id'=>'required|integer|min:1|exists:business,id',
        ];
        $validator = Validator::make($items, $input);
        if ($validator->fails()) {
            return new JsonResponse(['errors' => $validator->errors()], 400);
        }

        $products = Product::whereBusinessId($items['business_id'])->get();
        return new JsonResponse($products, 200);
    }


}

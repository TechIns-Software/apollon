<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
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

    public function editProducts(Request $request)
    {
        $productsToModify = [];
        $validationRules=[
            'products'=>"required|array",
            'products.*'=>[
                "required",
                "string",
                function (string $attribute, mixed $value, \Closure $fail) use (&$productsToModify) {
                    $productId = (int)str_replace('products.',"",$attribute);
                    if($productId < 1){
                        $fail("Το id του προϊόντος δεν μπορεί να ειναι αρνητικό");
                        return;
                    }

                    $product = Product::find($productId);

                    if(empty($product)){
                        $fail("Αδυναμία Εύρεσης προϊόντος");
                        return;
                    }

                    $productsToModify[] = ['product'=>$product,'name'=>$value];
                }
            ]
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return new JsonResponse(['errors' => $validator->errors()], 400);
        }

        $modified = [];
        try{
            DB::beginTransaction();
            foreach ($productsToModify as $productInfo){
                dump($productInfo['product']);
                $product = $productInfo['product'];
                $product->name = $productInfo['name'];
                $product->save();
                $modified[]=$product;
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollback();
            report($e);
            return new JsonResponse(['errors' => "Αδυναμία αποθήκευση στην Βάση"], 500);
        }

        return new JsonResponse($modified, 200);
    }
}

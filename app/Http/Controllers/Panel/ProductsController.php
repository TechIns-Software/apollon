<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use View;

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

        return new Response(View::make('business.components.productListItem',['row'=>$product]), 201);
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

        $qb = Product::whereBusinessId($items['business_id']);

        $paginationUrlAppendData=['business_id'=>$items['business_id']];

        $searchterm = $request->input('name',null);
        if(!empty($searchterm)){
           $qb->where('name','like','%'.$searchterm.'%');
            $paginationUrlAppendData['name']=$searchterm;
        }
        $qb->orderBy('created_at','DESC')->orderBy('name','ASC');

        $cursor = $request->input('cursor', null);
        if(!empty($cursor)) {
            $products = $qb->cursorPaginate(20, ['*'], 'cursor', $cursor);
        } else {
            $products = $qb->cursorPaginate(20);
        }

        $products=$products->appends($paginationUrlAppendData);

        return response()->view('business/components/listProducts',['rows'=>$products])
                ->header("X-NextUrl",$products->nextPageUrl())
                ->header("X-HasMore",$products->hasMorePages());
    }

    /**
     *
     * As you'll see upon frontend I edit them mone at a time but this route mass edits products
     * The frontend has been implemented later because the initial planning was for another person to implement it.
     *
     * Also, there was no initial design upon product edit thus I made it generic.
     *
     * @author DESYLLAS DIMITRIOS <ddesyllas@techins.gr>
     *
     * @param Request $request
     * @return JsonResponse
     */
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
                        $fail("Το id του προϊόντος δεν μπορεί να είναι αρνητικό");
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

<?php

namespace App\Http\Controllers\API;

use App\Http\Middleware\MissingIdBaseMiddleware;
use App\Http\Middleware\RequiresClientId;
use App\Http\Middleware\RequiresOrderId;
use App\Http\Resources\OrderResource;
use App\Models\Client;
use App\Models\Order;

use App\Models\Product;
use App\Models\ProductOrder;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class OrderController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware(RequiresOrderId::class,['edit','order','delete','addItemToOrder','removeOrderProduct'])
        ];
    }

    /**
     * @throws ValidationException
     */
    public function add(Request $request):JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'client_id' => [
                'required',
                'integer',
                'min:1',
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    $client = Client::find($value);

                    if(empty($client)){
                        $fail("Client is not found");
                        return;
                    }

                    if($client->business_id != $user->business_id ){
                        $fail("Client is not found");
                    }
                }
            ],
            'status' => 'required|string|in:OPEN,FINISHED,CANCELLED',
            'description' => 'sometimes|nullable'
        ]);

        if ($validator->fails()) {
           throw new ValidationException($validator);
        }

        $items = $request->all();

        $items['saas_user_id']=$user->id;
        $items['business_id']=$user->business_id;

        try {
            $order = Order::create($items);
        } catch (\Exception $e) {
            report($e);
            return new JsonResponse(['message' => 'Αδυναμία αποθήκευσης'], 500);
        }

        return new JsonResponse(new OrderResource($order), 201);
    }


    public function list(Request $request)
    {
        $user = $request->user();

        $page = $request->get('page')??1;
        $limit = $request->get('limit')??20;

        if($page <= 0){
            return new JsonResponse(['msg'=>"Page must have positive value"],400);
        }

        if($limit <= 0){
            return new JsonResponse(['msg'=>"Limit must have positive value"],400);
        }

        $orders = Order::whereBusinessId($user->business_id)
            ->orderBy('created_at','DESC')->offset(($page - 1) * $limit)
            ->simplePaginate($limit);

        $orders->appends(['limit'=>$limit, 'page' => $page+1]);

        return new JsonResponse($orders,200);
    }

    public function order(Request $request)
    {
        $order = $request->input('order');
        return new OrderResource($order);
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        $items = $request->all();

        /*
         * This route NEEDS RequiresOrderId in order to work.
         * Please do nor remove middleware for the route of this controller,
         * without refactoring the 2 lines bellow.
         */
        $order = $items['order'];
        unset($items['order']);

        $validator = Validator::make($items, [
            'client_id' => [
                'sometimes',
                'integer',
                'min:1',
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    $client = Client::find($value);

                    if(empty($client)){
                        $fail("Client is not found");
                        return;
                    }

                    if($client->business_id != $user->business_id ){
                        $fail("Client is not found");
                    }
                }
            ],
            'status' => 'sometimes|string|in:OPEN,FINISHED,CANCELLED',
            'description' => 'sometimes|nullable',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try{
            $order->update($items);
        } catch (\Exception $e){
            report($e);
            return new JsonResponse(['message' => 'Αδυναμία αποθήκευσης'], 500);
        }

        return new JsonResponse($order,200);
    }

    public function delete(Request $request)
    {
        $order = $request->input('order');
        try{
            $order->delete();
        } catch (\Exception $e){
            report($e);
            return new JsonResponse(['message' => 'Αδυναμία αποθήκευσης'], 500);
        }

        return new JsonResponse(['msg'=>'Η παραγγελία διεγράφει επιτυχώς'],200);
    }

    public function addItemToOrder(Request $request)
    {
        $items = ['items'=>$request->get('items')];
        /*
         * This route NEEDS RequiresOrderId in order to work.
         * Please do nor remove middleware for the route of this controller,
         * without refactoring the 2 lines bellow.
         */
        $order = $request->input('order');

        /**
         * @var Product
         */
        $products = [];
        $productIds = [];
        $productSearch = ProductOrder::whereOrderId($order->id);

        $validator = Validator::make($items, [
            'items' => 'required|array',
            'items.*'=>[
                "required",
                'numeric',
                function (string $attribute, mixed $value, \Closure $fail) use (&$products,$order,&$productIds){

                    $productId = (int)str_replace('items.',"",$attribute);
                    $productInDb = Product::find($productId);
                    if(empty($productInDb)){
                        $fail("Το προϊόν δεν υπάρχει");
                        return;
                    }
                    if($productInDb->business_id != $order->business_id){
                        $fail("Αδυναμία Επεξεργασίας");
                        return;
                    }
                    // I want to avoid extra loops therefore I prepare my input here
                    $products[]=[
                        'product_id'=>$productId,
                        'ammount'=>(float)$value,
                        'order_id'=>$order->id
                    ];
                    $productIds[]=$productId;
                }
            ]
        ]);


        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if(empty($productIds)){
            return new JsonResponse(['msg'=>"Δεν δώθηκαν τιμές για τα προϊόντα"],400);
        }

        $productSearch=$productSearch->whereIn('product_id',$productIds);

        $created = [];
        try{
            ProductOrder::upsert($products,['product_id','order_id'],['ammount']);
            $created =$productSearch->with('product')->get();
        }catch (\Exception $e){
            report($e);
            return new JsonResponse(['msg'=>"Αδυναμία αποθήκευσης"],500);
        }

        return new JsonResponse($created,200);
    }

    public function removeOrderProduct(Request $request)
    {
        $order = $request->get('order');
        $product_id = $request->route('product_id');

        if(empty($product_id)){
            return new JsonResponse(['msg'=>"Το προϊόν δεν υπάρχει"],404);
        }

        $product = Product::find($product_id);

        if(empty($product)){
            return new JsonResponse(['msg'=>"Το προϊόν δεν υπάρχει"],404);
        }

        if($order->business_id != $product->business_id){
            return new JsonResponse(['msg'=>"Αδυναμία διαγραφής."],403);
        }

        try{
            ProductOrder::where('product_id',$product->id)->where('order_id',$order->id)->delete();
        }catch (\Exception $e){
            report($e);
            return new JsonResponse(['msg'=>"Αδυναμία Διαγραφής"],500);
        }

        return new JsonResponse(['msg'=>"Επιτυχώς Διεγράφει"],200);
    }

}

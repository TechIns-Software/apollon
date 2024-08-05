<?php

namespace App\Http\Controllers\API;

use App\Http\Middleware\RequiresOrderId;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductOrderResource;
use App\Http\Validation\OrderValidationClosure;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\Delivery;
use App\Models\DeliveryOrder;

use App\Rules\ValidateBoolean;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\DB;

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
        $productsToInsert=[];

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
            'description' => 'sometimes|nullable',
            'items' => 'sometimes|array|nullable',
            'items.*'=>[
                "required",
                'numeric',
                function (string $attribute, mixed $value, \Closure $fail) use ($user,&$productsToInsert) {
                    if(OrderValidationClosure::validateOrderItems($attribute,$value,$fail,$user->business_id,$product)){
                        $productsToInsert[]=[
                            'product_id'=>$product->id,
                            'ammount'=>(float)$value,
                        ];
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
           throw new ValidationException($validator);
        }

        $items = $request->all();

        $items['saas_user_id']=$user->id;
        $items['business_id']=$user->business_id;

        try {
            DB::beginTransaction();

            $order = Order::create($items);
            foreach ($productsToInsert as $key=>$product){
                $productsToInsert[$key]['order_id']=$order['id'];
            }

            ProductOrder::insert($productsToInsert);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            report($e);
            return new JsonResponse(['message' => 'Αδυναμία αποθήκευσης'], 500);
        }

        return new JsonResponse(new OrderResource($order), 201);
    }


    /**
     * @throws ValidationException
     */
    public function list(Request $request)
    {
        $user = $request->user();

        $rules = ["without_delivery"=>[
            'sometimes',
            new ValidateBoolean()
        ]];

        // function in helpers.php
        validatePaginationAndSortening($request->all(),$rules);

        // Input is validated Above
        $page = $request->get('page')??1;
        $limit = $request->get('limit')??20;

        $qb = Order::where(Order::TABLE.".business_id",$user->business_id)
            ->join(Client::TABLE,Client::TABLE.'.id','=',Order::TABLE.'.client_id')
            ->select(
                Order::TABLE.".*",
                Client::TABLE.'.surname as client_surname',
                Client::TABLE.'.name as client_name',
                Client::TABLE.'.region as client_region',
                Client::TABLE.".nomos as client_nomos"
            );
        if($request->has('without_delivery')){

            $closure = function(\Illuminate\Database\Query\Builder $q) use ($user){
                $q->select('order_id')
                    ->from(DeliveryOrder::TABLE)
                    ->join(Delivery::TABLE,Delivery::TABLE.".id","=",DeliveryOrder::TABLE.".delivery_id")
                    ->where(Delivery::TABLE.".business_id",$user->business_id);
            };
            $without_delivery = parseBool($request->get("without_delivery"))??false;

            if($without_delivery){
                $qb->whereNotIn(Order::TABLE.'.id',$closure);
            } else {
                $qb->whereIn(Order::TABLE.'.id',$closure);
            }
        }

        if($request->has('searchterm')){
            $searchterm = $request->get('searchterm')??null;
            if (!empty($searchterm)){
                $qb->where('description','like','%'.$searchterm.'%');
            }
        }

        $orderBy = $request->get('order_by');
        $order = $request->get('ordering');

        if(!empty($orderBy) && !empty($order)){

            switch($orderBy){
                case 'client_name':
                    $qb->orderBy(Client::TABLE.'.surname',$order)
                        ->orderBy(Client::TABLE.'.name',$order);
                    break;
                case 'area':
                    $qb->orderBy(Client::TABLE.'.region',$order)
                        ->orderBy(Client::TABLE.'.nomos',$order);
                    break;
            }
        }

        if($request->has('page') && $request->has('limit')){
            $orders = $qb->offset(($page - 1) * $limit)
                ->simplePaginate($limit);
            $orders->appends(['limit'=>$limit, 'page' => $page+1]);
        } else {
            $orders = $qb->get();
            $orders = ['data'=>$orders];
        }

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
            ProductOrder::whereOrderId($order->id)->delete();
            DeliveryOrder::whereOrderId($order->id)->delete();
            $order->delete();
        } catch (\Exception $e){
            report($e);
            return new JsonResponse(['message' => 'Αδυναμία αποθήκευσης'], 500);
        }

        return new JsonResponse(['msg'=>'Η παραγγελία διεγράφη επιτυχώς'],200);
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
                    if(OrderValidationClosure::validateOrderItems($attribute,$value,$fail,(int)$order->business_id,$product)){
                        // I want to avoid extra loops therefore I prepare my input here
                        $products[]=[
                            'product_id'=>$product->id,
                            'ammount'=>(float)$value,
                            'order_id'=>$order->id
                        ];
                        $productIds[]=$product->id;
                    }
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

        return new JsonResponse(ProductOrderResource::collection($created),200);
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

    /**
     *
     * Saas User does not add or modify products.
     * Therefore, making a WHOLE controller for it seems like a waste.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function productSearch(Request $request)
    {
        $user = $request->user();
        $searchterm = $request->get('searchterm');

        $productsQB = Product::where('business_id',$user->business_id);

        if(!empty($searchterm)){
            $productsQB->where('name','like',"%{$searchterm}%");
        }

        $products = $productsQB->get();

        return new JsonResponse($products,200);
    }
}

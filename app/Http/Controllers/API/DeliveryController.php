<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Http\Middleware\RequiresOrderId;
use App\Http\Resources\DeliveryOrderResource;
use App\Http\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Models\DeliveryOrder;
use App\Models\Driver;
use App\Models\Order;
use App\Http\Middleware\RequiresDeliveryId;

use App\Models\ProductOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\Middleware;

class DeliveryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(RequiresDeliveryId::class,['edit','delivery','deliveries','addOrderToDelivery','delete'])
        ];
    }
    public function add(Request $request)
    {
        $user = $request->user();
        $all = $request->all();

        $driver = null;

        $validator = Validator::make($all,[
           'driver_id'=>[
               'required_without:driver_name',
               'integer',
               "min:1",
               function ($attribute, $value, $fail) use (&$driver,$user) {
                    $driver = Driver::find($value);
                    if(empty($driver)){
                        $fail("Ο οδηγός δεν βρέθηκε");
                    }

                    if($driver->business_id != $user->business_id){
                        $fail("Ο οδηγός δεν βρέθηκε");
                    }
               }
           ],
           'driver_name'=>[
            'required_without:driver_id',
           ],
            "name"=>[
                'required',
                "string"
            ],
            "orders"=>[
                "sometimes",
                "array"
            ],
            "orders.*"=>[
                "required",
                "integer",
                "min:1",
                function ($attribute, $value, $fail) use ($user){
                    $order = Order::find($value);
                    if(empty($order)){
                        $fail("Δεν βρέθηκε η παραγγελία");
                    }

                    if($order->business_id != $user->business_id){
                        $fail("Δεν βρέθηκε η παραγγελία");
                    }
                }
            ]
        ]);
        if($validator->fails()){
            throw new ValidationException($validator);
        }

        try {
            DB::beginTransaction();

            if(empty($driver)){
                $driver = Driver::create(['driver_name'=>$all['driver_name'],'business_id'=>$user->business_id]);
            }
            $delivery = Delivery::create([
                'delivery_date'=>$all['delivery_date'],
                'name'=>$all['name'],
                'driver_id'=>$driver->id,
                'business_id'=>$user->business_id,
            ]);

            $orders = collect();
            $all['orders'] = $all['orders']??[];
            foreach ($all['orders'] as $key => $order){
                $orders->push(DeliveryOrder::create([
                   'order_id'=>$order,
                   'delivery_id'=>$delivery->id,
                   'delivery_sequence'=>$key+1
                ]));
            }

            $delivery->setRelation('deliveryOrder',$orders);
            $delivery->setRelation('driver',$driver);
            DB::commit();
        }catch (\Exception $e){
            DB::rollback();
            report($e);
            return response()->json(['msg' => "Αδυναμία αποθήκευσης"], 500);
        }

        return new JsonResponse(new DeliveryResource($delivery),201);
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        $all = $request->all();

        /**
         * @var Delivery
         */
        $delivery = $all['delivery'];
        unset($all['delivery']);

        $validator = Validator::make($all,[
            'driver_id'=>[
                'sometimes',
                'integer',
                "min:1",
                function ($attribute, $value, $fail) use ($user) {
                    $driver = Driver::find($value);
                    if(empty($driver)){
                        $fail("Ο οδηγός δεν βρέθηκε");
                        return;
                    }

                    if($driver->business_id != $user->business_id){
                        $fail("Ο οδηγός δεν βρέθηκε");
                    }
                }
            ],
            "name"=>[
                'sometimes',
                "string"
            ],
            "orders"=>[
                "sometimes",
                "array"
            ],
            "orders.*"=>[
                "sometimes",
                "integer",
                "min:1",
                function ($attribute, $value, $fail) use ($user){
                    $order = Order::find($value);
                    if(empty($order)){
                        $fail("Δεν βρέθηκε η παραγγελία");
                    }

                    if($order->business_id != $user->business_id){
                        $fail("Δεν βρέθηκε η παραγγελία");
                    }
                }
            ]
        ]);
        if($validator->fails()){
            throw new ValidationException($validator);
        }

        try {
            DB::beginTransaction();

            $delivery->update($all);
            $delivery->refresh();
            $orders = collect();
            $all['orders'] = $all['orders']??[];
            foreach ($all['orders'] as $key => $order){
                $orders->push(DeliveryOrder::upsert([
                    'order_id'=>$order,
                    'delivery_id'=>$delivery->id,
                    'delivery_sequence'=>$key+1
                ],['order_id','delivery_id']));
            }

            $delivery->refresh();
            DB::commit();
        }catch (\Exception $e){
            DB::rollback();
            report($e);
            return response()->json(['msg' => "Αδυναμία αποθήκευσης"], 500);
        }

        return new JsonResponse(new DeliveryResource($delivery),200);
    }

    public function delivery(Request $request)
    {
        return new JsonResponse(new DeliveryResource($request->input('delivery')),200);
    }

    public function list(Request $request)
    {
        $all = $request->all();

        $validationRules=[
            "delivery_date_from"=>[
                "sometimes",
                "date"
            ],
            "delivery_date_until"=>[
                "sometimes",
                "date",
                "after:delivery_date_from",
            ],
            "name"=>[
                'sometimes',
                "string"
            ],
            'page'=>[
                'sometimes',
                'integer',
                "min:1"
            ],
            'limit'=>[
                'sometimes',
                'integer',
                "min:1"
            ],
        ];
        $validator = Validator::make($all,$validationRules);
        if($validator->fails()){
            throw new ValidationException($validator);
        }

        $user = $request->user();

        $qb = Delivery::where('business_id',$user->business_id)->orderBy('delivery_date','DESC');

        $date = $request->get('delivery_date_from');
        if(!empty($date)){
            $qb=$qb->where('delivery_date',">=",$date);
        }

        $date = $request->get('delivery_date_until');
        if(!empty($date)){
            $qb=$qb->where('delivery_date',"<=",$date);
        }

        $searchterm = $request->get('name');
        if(!empty($searchterm)){
            $qb=$qb->whereRaw("MATCH(name) AGAINST(? IN BOOLEAN MODE)",["*".$searchterm."*"])
                ->orderByRaw("MATCH(name) AGAINST(?) DESC", ["*".$searchterm."*"]);
        }

        $page = $request->get('page')??1;
        $limit = $request->get('limit')??20;
        $results = $qb->offset(($page - 1) * $limit)
            ->simplePaginate($limit);

        $appends = $all;
        $appends['limit']=$limit;
        $appends['page']=$page+1;
        $results->appends($appends);

        return new JsonResponse($results,200);
    }

    public function driver(Request $request)
    {
        $user = $request->user();
        $qb = Driver::where('business_id',$user->business_id)->orderBy('driver_name');

        $searchterm = $request->get('driver_name');
        if(!empty($searchterm)){
            $qb=$qb->whereRaw("MATCH(driver_name) AGAINST(? IN BOOLEAN MODE)",["*".$searchterm."*"])
                ->orderByRaw("MATCH(driver_name) AGAINST(?) DESC", ["*".$searchterm."*"]);
        }

        return new JsonResponse($qb->get());
    }

    public function delete(Request $request)
    {
        $delivery = $request->input('delivery');

        try{
            $delivery->delete();
        }catch (\Exception $e){
            report($e);
            return new JsonResponse(['msg'=>"Αδυναμία Διαγραφής"],500);
        }

        return new JsonResponse(['msg'=>'Επιτυχής Διαγραφής']);
    }

    public function changeSequenceOfOrders(Request $request,int $id)
    {
        if($id <= 0){
            return new JsonResponse(['msg'=>"Αδυναμία εύρεσης"],404);
        }

        $deliveryOrder = DeliveryOrder::find($id);

        if(empty($deliveryOrder)){
            return new JsonResponse(['msg'=>"Αδυναμία εύρεσης"],404);
        }

        $user = $request->user();
        $order = $deliveryOrder->order;
        if($user->business_id != $order->business_id){
            return new JsonResponse(['msg'=>"Απαγορεύετε"],403);
        }

        $delivery = $deliveryOrder->delivery;
        if($user->business_id != $delivery->business_id){
            return new JsonResponse(['msg'=>"Απαγορεύετε"],403);
        }

        $sequenceNum = $request->get('delivery_order');
        if(empty($sequenceNum) || ! is_numeric($sequenceNum)){
            return new JsonResponse(['msg'=>"Η σειρά δεν έχει έγκυρη τιμή"],400);
        }

        $deliveryOrder->delivery_sequence=$sequenceNum;

        try{
            $deliveryOrder->save();
        }catch (\Exception $e){
            report($e);
            return new JsonResponse(['msg'=>"Αδυναμία Αποθήκευσης"],500);
        }

        return new JsonResponse(new DeliveryOrderResource($deliveryOrder),200);
    }
}

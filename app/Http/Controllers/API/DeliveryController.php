<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Http\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Models\DeliveryOrder;
use App\Models\Driver;
use App\Models\Order;
use App\Http\Middleware\RequiresDeliveryId;

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
            new Middleware(RequiresDeliveryId::class,['edit','delivery'])
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
            "delivery_date"=>[
                "required",
                "date"
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
                    }

                    if($driver->business_id != $user->business_id){
                        $fail("Ο οδηγός δεν βρέθηκε");
                    }
                }
            ],
            "delivery_date"=>[
                "required",
                "date"
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


}

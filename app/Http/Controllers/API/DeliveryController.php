<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Http\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Models\DeliveryOrder;
use App\Models\Driver;
use App\Models\Order;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
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
            dump($e->getMessage());
            DB::rollback();
            report($e);
            return response()->json(['msg' => "Αδυναμία αποθήκευσης"], 500);
        }

        return new JsonResponse(new DeliveryResource($delivery),201);
    }
}

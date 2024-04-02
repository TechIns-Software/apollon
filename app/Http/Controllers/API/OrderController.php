<?php

namespace App\Http\Controllers\API;

use App\Http\Middleware\MissingIdBaseMiddleware;
use App\Http\Middleware\RequiresClientId;
use App\Http\Middleware\RequiresOrderId;
use App\Models\Client;
use App\Models\Order;

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
            new Middleware(RequiresOrderId::class,['edit','order','delete'])
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

        return new JsonResponse($order, 201);
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

    public function order(Request $request, Order $order)
    {
        $order->refresh();
        return new JsonResponse($order,200);
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

        return new JsonResponse(['msg'=>'Η παραγγελίεα διεγράφει επιτυχώς'],200);
    }
}

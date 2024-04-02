<?php

namespace App\Http\Controllers\API;

use App\Http\Middleware\RequiresClientId;
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
        // @todo Implement checks for order id IN url
        return [
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
                Rule::exists('client','id')
            ],
            'status' => 'required|string|in:OPEN,FINISHED,CANCELLED',
            'description' => 'sometimes|nullable'
        ]);

        if ($validator->fails()) {
           throw new ValidationException($validator);
        }

        try {
            $items = $request->all();
            $items['saas_user_id']=$user->id;
            $items['business_id']=$user->business_id;

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

    public function edit(Request $request, Order $order)
    {
        // @todo Implement
        return new JsonResponse($order,201);
    }
}

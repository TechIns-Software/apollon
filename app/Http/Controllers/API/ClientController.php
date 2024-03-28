<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function list(Request $request)
    {
        $page = (int)$request->get('page')??1;
        $limit = (int)$request->get('limit')??20;

        $clients = Client::orderBy('id','DESC')
            ->orderBy('created_at','DESC')->offset(($page - 1) * $limit)
            ->paginate($limit);
        $clients->appends(['limit'=>$limit]);

        return new JsonResponse($clients,200);
    }

    public function client(Request $request)
    {
        $client = $request->input('client');
        return new JsonResponse($client,200);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'surname' => 'required|string',
            'telephone' => 'sometimes|nullable|string|regex:/[\\+\d\s]+/',
            'phone1' => 'sometimes|nullable|string|regex:/[\\+\d\s]+/',
            'phone2' => 'sometimes|nullable|string|regex:/[\\+\d\s]+/',
            'state' => 'sometimes|string',
            'region' => 'sometimes|string',
            'description' => 'sometimes|string',
            'map_link' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $request->all();
        $data['business_id']=$user->business_id;
        $data['saas_user_id']=$user->id;
        $client = Client::create($data);

        return response()->json($client, 201);
    }

    public function edit(Request $request)
    {

    }
}

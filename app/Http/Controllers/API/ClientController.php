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
        $user = $request->user();
        $page = (int)$request->get('page')??1;
        $limit = (int)$request->get('limit')??20;

        $clients = Client::orderBy('id','DESC')
            ->whereBusinessId($user->business_id)
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
            'telephone' => 'sometimes|nullable|string',
            'phone1' => 'sometimes|nullable|string',
            'phone2' => 'sometimes|nullable|string',
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
        $data = $request->all();
        /**
         * @var Client
         */
        $client = $data['client'];
        unset($data['client']);
        if(empty($data)){
            return response()->json(['errors' => "No data provided"], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'surname' => 'sometimes|string',
            'telephone' => 'sometimes|nullable|string',
            'phone1' => 'sometimes|nullable|string',
            'phone2' => 'sometimes|nullable|string',
            'state' => 'sometimes|string',
            'region' => 'sometimes|string',
            'description' => 'sometimes|string',
            'map_link' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try{
            $client->update($data);
            $client->refresh();
        } catch (\Exception $e){
            report($e);
            return response()->json(['errors' => "Αδυναμία αποθήκευσης"], 500);
        }

        return response()->json($client, 200);
    }

    public function delete(Request $request)
    {
        /**
         * @var Client
         */
        $client = $request->input('client');
        $id = $client->id;
        try{
            $client->delete();
        }catch (\Exception $e){
            report($e);
            return response()->json(['errors' => "Αδυναμία αποθήκευσης"], 500);
        }

        return response()->json( ['id'=>$id,'msg'=>'Ο πελάτης διεγράφει'],200);
    }
}

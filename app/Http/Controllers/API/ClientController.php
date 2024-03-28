<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function list(Request $request)
    {

    }

    public function client(Request $request)
    {

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

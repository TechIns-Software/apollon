<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Driver;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DriverController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();
        $all = $request->all();
        $validationRules = [
          'name' => 'required|string',
        ];

        $validator = Validator::make($all, $validationRules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $driver = Driver::create(['driver_name'=>$all['name'],'business_id'=>$user->business_id]);

        return new JsonResponse($driver,201);
    }

    public function list(Request $request)
    {
        $user = $request->user();
        $qb = Driver::where('business_id',$user->business_id)->orderBy('driver_name');

        $searchterm = $request->get('name');
        if(!empty($searchterm)){
            $qb=$qb->whereRaw("MATCH(driver_name) AGAINST(? IN BOOLEAN MODE)",["*".$searchterm."*"])
                ->orderByRaw("MATCH(driver_name) AGAINST(?) DESC", ["*".$searchterm."*"]);
        }

        return new JsonResponse($qb->paginate());
    }

    public function edit(Request $request,$id)
    {
        $user = $request->user();

        $driver = Driver::find($id);

        if(empty($driver)){
            return new JsonResponse(['msg'=>"Driver not found"],404);
        }

        if($driver->business_id != $user->business_id){
            return new JsonResponse(['msg'=>"You are not allowed to edit this Driver"],403);
        }

        $all = $request->all();
        $validationRules = [
            'name' => 'required|string',
        ];

        $validator = Validator::make($all, $validationRules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $driver->driver_name = $all['name'];
        $driver->save();

        return new JsonResponse($driver,200);
    }
}

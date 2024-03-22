<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Rules\ValidateBoolean;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Js;

class BusinessController extends Controller
{
    public function create(Request $request)
    {
        $rules=[
            'name'=>"required",
            'active'=>["required",new ValidateBoolean()],
            "expiration_date"=>"required|date",
            "vat"=>"sometimes|nullable|regex:/^[0-9]{9}$/i'",
            "doy"=>"sometimes|nullable"
        ];

        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()){
            return new JsonResponse(['msg'=>$validator->errors()],400);
        }

        $business = new Business();
        $business->name = $request->get('name');

        $business->vat = $request->get('vat_num');
        $business->doy = $request->get('doy');

        $business->is_active = $request->get('active');
        $business->expiration_date = $request->get('expiration_date');
        try{
            $business->save();
        }catch (\Exception $e){
            report($e);
            return new JsonResponse(['msg'=>"Αδυναμία αποθήκευσης"],500);
        }

        return new JsonResponse($business,201);
    }

    public function list(Request $request)
    {
        $searchterm = $request->get('searchterm');
        $cursor = $request->get('cursor');

        $query = Business::orderBy('id');

        if(!empty($searchterm)){
            $query = $query->where('name','like',"%".$searchterm."%");
        }

        if(!empty($cursor)){
            $result = $query->cursorPaginate(100,['*'], 'cursor',$cursor);
            return view('components.listBusiness',['rows'=>$result]);
        } else {
            $result = $query->cursorPaginate(100);
        }

        return view('business.list',['businesses'=>$result]);
    }
}

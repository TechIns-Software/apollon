<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Js;

class BusinessController extends Controller
{
    public function create(Request $request)
    {
        $name = $request->get('name');
        if(empty($name)){
            return new JsonResponse(['msg'=>"Το όνομα της επιχείρησης δεν δώθηκε"],400);
        }

        $business = new Business();
        $business->name = $name;

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

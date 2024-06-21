<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use App\Rules\ValidateBoolean;
use App\Services\Stats\BusinessStats;
use App\Services\Stats\OrderStats;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function businessStats(Request $request)
    {
        $years = $request->input('year');
        $stats = new BusinessStats($years);

        return new JsonResponse($stats->getStats());
    }
    public function orderStats(Request $request,int $businesId)
    {
        $years = $request->get('year',[(int)Carbon::now()->format('Y')]);

        $orderStats = new OrderStats($businesId,$years);

        return new JsonResponse($orderStats->getStats());
    }


    public function list(Request $request)
    {
        $searchterm = $request->get('name');
        $cursor = $request->get('cursor');

        $query = Business::query();
        if(!empty($searchterm)){
            $query = $query->where('name','like',"%".$searchterm."%");
        }

        $query->orderBy('name');

        if(!empty($cursor)){
            $result = $query->cursorPaginate(100,['*'], 'cursor',$cursor);
        } else {
            $result = $query->cursorPaginate(100);
        }

        if($request->ajax()){
            return view('components.business.listBusiness',['rows'=>$result]);
        }

        return view('business.list',['businesses'=>$result,'name'=>$searchterm]);
    }


    public function get(Request $request,int $business_id)
    {
        $business = Business::findOrFail($business_id);
        $products = Product::where("business_id",$business->id)->orderBy('created_at','DESC')->orderBy('name','ASC')
            ->cursorPaginate(20)
            ->withPath('/products?business_id='.$business->id);
        return view("business.info",['business'=>$business,'products'=>$products]);

    }

    public function edit(Request $request)
    {
        $business = null;
        $rules=[
            'business_id'=>[
                'required',
                'min:1',
                function (string $attribute, mixed $value, \Closure $fail) use (&$business) {
                    $business = Business::find($value);
                    if(empty($business)){
                        $fail('Η εταιρεία δεν υπάρχει');
                    }
                }
            ],
            'name'=>"sometimes|nullable|string",
            'active'=>["sometimes",'nullable',new ValidateBoolean()],
            "expiration_date"=>"sometimes|nullable|date",
            "vat_num"=>"sometimes|nullable|regex:/^[0-9]{9}$/i",
            "doy"=>"sometimes|nullable"
        ];

        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()){

            $errors = $validator->errors();

            // Check if there's an error associated with the 'business_id' field
            if ($errors->has('business_id')) {
                return new JsonResponse(['msg'=>"Η εταιρεία δεν υπάρχει"],404);
            }
            return new JsonResponse(['msg'=>$errors],400);
        }

        $save=false;

        if(!empty($request->name)){
            $business->name = $request->name;
            $save=true;
        }

        if($request->has('expiration_date')){
            $business->expiration_date = $request->expiration_date;
            $save=true;
        }

        if(!is_null($request->active)){
            $business->is_active = $request->active;
            $save=true;
        }

        if($request->has('vat_num')){
            $business->vat = $request->vat_num;
            $save=true;
        }

        if($request->has('doy')){
            $business->doy = $request->doy;
            $save=true;
        }

        if(!$save){
            return new JsonResponse(['msg'=>"Μη αποθήκευση αλλαγών λόγο ότι δεν δώθηκαν στοιχεία"],422);
        }

        try{
            $business->save();
        }catch (\Exception $e){
            report($e);
            return new JsonResponse(['msg'=>"Αδυναμία αποθήκευσης"],500);
        }

        return new JsonResponse($business,200);
    }


}

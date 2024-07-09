<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\SaasUser;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SaasUserController extends Controller
{

    public function add(Request $request)
    {
        $rules = [
            'business_id'=>[
                "required",
                "integer",
                "min:1",
                Rule::exists("business","id")
            ],
            'email'=>[
                "required",
                "string",
                "email"
            ],
            "password"=>[
                "required",
                "string",
                "confirmed"
            ],
            "name"=>[
                "required",
                "string"
            ]
        ];

        $errors = [
            "business_id"=>"Παρακαλώ δώστε ένα έγκυρο Id Εταιρείας",
            "email.required"=>"Το email απαιτείτε.",
            "email"=>"H τιμή δεν είναι έγγυρη.",
            "password.required"=>"Η τιμή απαιτείτε",
            "password.confirmed"=>"Οι τιμές δεν ταιριάζουν",
            'name.required' => "Η τιμή απαιτείτε"
        ];

        $verifier = Validator::make($request->all(),$rules,$errors);

        if($verifier->fails()){
            return new JsonResponse($verifier->errors(),400);
        }

        $email = $request->get('email');
        $userExists = SaasUser::whereEmail($email)->exists();
        if($userExists){
            return new JsonResponse(['msg'=>"User with email {$email} already exists."],500);
        }

        try{
            $user = SaasUser::create([
                'email'=>$email,
                'password'=>Hash::make($request->get('password')),
                'name'=>$request->get('name'),
                'business_id'=>$request->get('business_id')
            ]);
        } catch (\Exception $e){
            report($e);
            return new JsonResponse(['msg'=>"An intenral error has occured"],500);
        }

        return new JsonResponse($user,201);
    }

    public function userInfo(Request $request,$user_id)
    {
        return "Hello";
    }

    public function edit(Request $request)
    {
        /**
         * @var SaasUser|null
         */
        $user = null;
        $rules = [
            'user_id'=>[
                "required",
                "integer",
                "min:1",
                function ($attribute, $value, $fail)  use (&$user){
                    $user = SaasUser::find($value);
                    if(empty($user)){
                        $fail("Ο χρήστης δεν υπάρχει");
                    }
                }
            ],
            'email'=>[
                "sometimes",
                "nullable",
                "string",
                "email"
            ],
            "password"=>[
                "sometimes",
                "nullable",
                "string",
                "confirmed"
            ],
            "name"=>[
                "sometimes",
                "nullable",
                "string"
            ]
        ];

        $errors = [
            "user_id"=>"Παρακαλώ δώστε ένα έγκυρο Id Εταιρείας",
            "email"=>"H τιμή δεν είναι έγγυρη.",
            "password.confirmed"=>"Οι τιμές δεν υπάρχουν"
        ];

        $verifier = Validator::make($request->all(),$rules,$errors);

        if($verifier->fails()){
            $errors = $verifier->errors();
            if($errors->has('user_id')){
                return new JsonResponse(['msg'=>"Ο χρήστης δεν υπάρχει."],404);
            }
            return new JsonResponse($errors,400);
        }

        $save=false;
        if(!empty($request->email)){
            $user->email = $request->email;
            $save=true;
        }

        if(!empty($request->name)){
            $user->name = $request->name;
            $save=true;
        }

        if(!empty($request->password)){
            $user->password = Hash::make($request->password);
            $save=true;
        }

        if(!$save){
            return new JsonResponse(['msg'=>"Δεν δώθηκαν στοιχεία για αποθήκευση"],422);
        }

        try{
            $user->save();
        }catch (\Exception $e){
            report($e);
            return new JsonResponse(['msg'=>"Αδυναμίας αποθήκευσης"],500);
        }

        return new JsonResponse($user,200);
    }

    public function list(Request $request)
    {
        $business_id = $request->get('business_id');
        if(empty($business_id)){
            return new JsonResponse(['msg'=>'H εταιρεία δεν υπάρχει'],404);
        }

        $qb = SaasUser::orderBy('id')->where('business_id',$business_id);

        $searchterm = $request->get('searchterm');

        if(!empty($searchterm)){
            $qb->where('name','like','%'.$searchterm.'%')
                ->orWhere('email','like','%'.$searchterm.'%');
        }

        $cursor = $request->input('cursor', null);
        if(!empty($cursor)) {
            $paginationResult = $qb->cursorPaginate(50, ['*'], 'cursor', $cursor);
        } else {
            $paginationResult = $qb->cursorPaginate(50);
        }

        return view('business.components.userList',['rows'=>$paginationResult]);
    }
}

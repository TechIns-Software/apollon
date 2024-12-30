<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\SaasUser;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SaasUserController extends Controller
{

    public function add(Request $request,int $id)
    {
        $business = $request->attributes->get('business');
        $rules = [
            'email'=>[
                "required",
                "string",
                "email",
                "unique:saas_user,email"
            ],
            "password"=>[
                "required",
                "string",
            ],
            "name"=>[
                "required",
                "string"
            ]
        ];

        $errors = [
            "email.required"=>"Το email απαιτείτε.",
            "email"=>"H τιμή δεν είναι έγγυρη.",
            "email.unique"=>"Ο χρήστης με το email αυτό ήδη υπάρχει",
            "password.required"=>"Η τιμή απαιτείτε",
            "password.confirmed"=>"Οι τιμές δεν ταιριάζουν",
            'name.required' => "Η τιμή απαιτείτε"
        ];

        $verifier = Validator::make($request->all(),$rules,$errors);

        if($verifier->fails()){
            return new JsonResponse(['msg'=>$verifier->errors()],400);
        }

        $email = $request->get('email');

        try{
            $user = SaasUser::create([
                'email'=>$email,
                'password'=>Hash::make($request->get('password')),
                'name'=>$request->get('name'),
                'business_id'=>$business->id
            ]);
        } catch (\Exception $e){
            report($e);
            return new JsonResponse(['msg'=>"An intenral error has occured"],500);
        }

        return response()->view('business.components.userListItem',['item'=>$user])->setStatusCode(201);
    }

    public function userInfo(Request $request,$user_id)
    {
        $user = SaasUser::findOrFail($user_id);
        return view('saasUser.saasUserEdit',['user'=>$user]);
    }

    public function edit(Request $request,$user_id)
    {

        /**
         * @var SaasUser|null
         */
        $user = SaasUser::findOrFail($user_id);
        $rules = [
            'email'=>[
                "sometimes",
                "nullable",
                "string",
                "email",
                function ($attribute, $value, $fail) use ($user) {
                    if ($value !== $user->email) {
                        $validator = Validator::make([$attribute => $value], [
                            $attribute => 'unique:saas_user,email'
                        ]);
                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));
                        }
                    }
                }
            ],
            "password"=>[
                "sometimes",
                "nullable",
                "string"
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
            "email.unique"=>"Ο χρήστης με το email αυτό ήδη υπάρχει",
        ];

        $verifier = Validator::make($request->all(),$rules,$errors);

        if($verifier->fails()){
            $errors = $verifier->errors();
            return redirect()->back()->withErrors($errors);
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
            return redirect()->back()->withErrors(['msg'=>"Δεν δώθηκαν στοιχεία για αποθήκευση"]);
        }

        try{
            $user->save();
        }catch (\Exception $e){
            report($e);
            return redirect()->back()->withErrors(['msg'=>"Αδυναμίας αποθήκευσης"]);
        }

        return redirect()->back()->with('message',"Ο χρήστης ενημερώθηκε επιτυχώς");
    }

    public function list(Request $request)
    {
        $business_id = $request->get('business_id');
        if(empty($business_id)){
            return new JsonResponse(['msg'=>'H εταιρεία δεν υπάρχει'],404);
        }

        /**
         * @var $qb Builder
         */
        $qb = SaasUser::orderBy('id')->where('business_id',$business_id);

        $appends = ['business_id'=>$business_id];

        $searchterm = $request->get('searchterm');

        if(!empty($searchterm)){
            $qb->where('name','like','%'.$searchterm.'%')
                ->orWhere('email','like','%'.$searchterm.'%');

            $appends['searchterm']=$searchterm;
        }

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 20);

        $paginationResult = $qb->simplePaginate($limit,page:$page);

        $paginationResult=$paginationResult->appends($appends);

        return response()->view('business.components.userList',['rows'=>$paginationResult])
            ->header('X-NextUrl',$paginationResult->nextPageUrl())
            ->header('X-HasMore',$paginationResult->hasMorePages());
    }
}

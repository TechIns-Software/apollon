<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function login()
    {
        if(!Auth::check()){
            return view('user.login');
        }

        return \redirect('/');
    }

    public function loginFormSubmit(Request $request)
    {
        if(Auth::guard('customer')->check()){
            Auth::guard('customer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Auth handles authentication and data sanitization
        if (!Auth::attempt(['email'=>$request['email'], 'password'=>$request['password']])) {
            return redirect('/login');
        }

        return \redirect('/');
    }

    /**
     * Logout as seen upon
     * https://laravel.com/docs/10.x/authentication#logging-out
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     */
    public function logout(Request $request)
    {
        $user = Auth::user()??Auth::guard('customer')->user();
        Auth::logout();
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if($user instanceof \App\Models\Customer){
            return redirect()->route('customer.login.page',['agent_id'=>$user->user_id]);
        }
        return redirect('/login');
    }

    public function register(Request $request)
    {

        if($request->method()=='POST'){
            $validator = Validator::make($request->all(),[
                'email'=>['required','email','unique:users,email'],
                'name'=>'required',
                'password'=>'required|confirmed',
                'role' => ['required', Rule::in(['ADMIN', 'USER'])],
                [
                    "role.in"=>'Ο ρόλος δεν ειναι εγγυρος',
                    'email.email'=>"To email δεν ειναι εγγυρο",
                    'email.required'=>"To email απαιτείτε",
                    'email.unique'=>"Το email ήδη υπάρχει",
                    'name.required'=>"Το πεδίο απαιτείτε",
                    'password.required'=>"Το πεδίο απαιτείτε"
                ]
            ]);

            if($validator->fails()){
                return Redirect::back()->withErrors($validator)->withInput();
            }

            $user = new User();
            $user->email = $request->get('email');
            $user->name = $request->get('name');
            $user->password = Hash::make($request->get('password'));
            $user->role = $request->get('role');

            try{
                $user->save();
                return Redirect::route('user.edit',['user_id'=>$user->id]);
            }catch (\Exception $e){
                return Redirect::back()
                    ->withError($e->getMessage())
                    ->withInput();
            }
        }

        return view('user.register_or_edit');
    }

    public function editUser(Request $request)
    {
        $user = User::findOrFail($request->get('user_id'));

        if($request->method()=='POST') {
            $validator = Validator::make($request->all(), [
                'email' => ['email'],
                'role' => [Rule::in([User::USER, User::ADMIN])],
                'password' => 'confirmed',
                [
                    "role.in" => 'Ο ρόλος δεν ειναι εγγυρος',
                    'email.email' => "To email δεν ειναι εγγυρο",
                    'email.required' => "To email απαιτείτε",
                    'email.unique' => "Το email ήδη υπάρχει",
                    'name.required' => "Το πεδίο απαιτείτε",
                    'password.required' => "Το πεδίο απαιτείτε"
                ]
            ]);

            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
            }

            $email = $request->get('email');
            if (!empty($email)) {
                $user->email = $email;
            }

            $name = $request->get('name', null);
            if (!empty($name)) {
                $user->name = $name;
            }

            $password = $request->get('password');
            if (!empty($password)) {
                $user->password = Hash::make($password);
            }

            $role = $request->get('role');
            if (!empty($role)) {
                $user->role = $role;
            }

            DB::beginTransaction();
            try {
                $user->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return Redirect::back()
                    ->withErrors($e->getMessage(), 'generic')
                    ->withInput();
            }

        }

        return view('user.register_or_edit',['user'=>$user]);
    }

    public function listUsers(Request $request)
    {

        $validator = Validator::make($request->all(),[
           'page'=>"integer|min:1",
           'limit'=>"integer|min:1",
           'role' => [Rule::in([User::USER, User::ADMIN,null])],
        ]);

        if($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $qb = User::query();

        $role = $request->get('role')??"";
        $role = trim($role);
        $role = strtoupper($role);
        if(!empty($role)){
            $qb->where('role',$role);
        }

        $searchTerm = $request->get('search')??"";
        $searchTerm = trim($searchTerm);

        if(!empty($searchTerm)){
            $qb->where(function ($query) use ($searchTerm){
                $query->where("email","like", "%".$searchTerm."%")
                    ->orWhere("name","like","%".$searchTerm."%");
            });
        }

        $page = $request->get('page')??1;
        $limit = $request->get('limit')??10;
        $paginationResult = $qb->offset(($page - 1) * $limit)->paginate($limit);

        $paginationResult->appends(['limit'=>$limit]);

        if($request->ajax()){
            return view('components/listUser',['users'=>$paginationResult]);
        }

        return view('user/listUsers',['users'=>$paginationResult]);
    }

    public function profile(Request $request)
    {
        $user = Auth::user();
        if ($request->method() == 'POST') {
            $validator = Validator::make($request->all(), [
                'password' => 'required:confirmed',
            ], [
                'password.required' => "Το πεδίο απαιτείτε",
                'password.confirmed' => "Τα πεδία θα πρέπει να έχουν την ίδια τιμή"
            ]);

            if($validator->fails()){
               return view('user/profile',['user'=>$user])->withErrors($validator);
            }

            // Both instances of User and Customer Have password
            $user->password = Hash::make($request->get('password'));
            try{
                $user->save();
            } catch (\Exception $e) {
               return view('user/profile',['user'=>$user])->withErrors('msg',$e->getMessage());
            }
        }

        return view('user/profile',['user'=>$user]);
    }



    public function passwordChangeForm(Request $request)
    {

    }
}

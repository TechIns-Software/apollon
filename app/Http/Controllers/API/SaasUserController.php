<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\APIUSerPasswordResetToken;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SaasUserController extends Controller
{
    private function guard()
    {
        return Auth::guard('mobile_api');
    }

    protected function getCredentialsFromBAsicAuth(Request $request): array
    {
        if (!$request->hasHeader('Authorization')) {
            throw new \InvalidArgumentException('Authorization Is missing');
        }
        $authorizationHeader = $request->header('Authorization');
        $authorizationHeader = str_replace('Basic ', '', $authorizationHeader);
        $authorizationHeader = base64_decode($authorizationHeader);

        return explode(':', $authorizationHeader);
    }

    public function login(Request $request)
    {
        try{
            list($email,$password) = $this->getCredentialsFromBAsicAuth($request);
        }catch (\Exception $e){
            new JsonResponse(['msg'=>'Το email και το password δεν έχουν δοθεί'],400);
        }

        if(auth()->guard('mobile_api_basic')->attempt(['email'=>$email,'password'=>$password])){
            $user = auth()->guard('mobile_api_basic')->user();
            $business = Business::find($user->business_id);

            if(!$business->is_active){
                return new JsonResponse(['msg'=>"H εταιρεία δεν ειναι ενεργή"],401);
            }
            return new JsonResponse(['token'=>$user->createToken('auth_token')->plainTextToken],201);
        }

        return new JsonResponse(['msg'=>"Η πρόσβαση Δεν επιτρέπετε"],401);
    }

    public function resetToken(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->json(['token' => $user->createToken('auth_token')->plainTextToken],201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->status(204);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'password'=>"required|confirmed"
        ]);

        if($validator->fails()){
            throw new ValidationException($validator);
        }

        $password=$request->get('password');
        $user = $request->user();

        try{
            $user->password = Hash::make($password);
            $user->save();
        }catch (\Exception $e){
            return response()->json(['msg'=>"Αδυναμία αλλαγής κωδικού πρόσβασης"],500);
        }
        return response()->json(['msg'=>"O κωδικός πρόσβασης άλλαξε επιτυχώς"],200);
    }

    public function sendPasswordResetEmail(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|exists:saas_user,email'
        ]);

        if($validator->fails()){
            throw new ValidationException($validator);
        }

        $broker =  Password::broker('saas_users');
        $user  = $broker->getUser(['email'=>$request->get('email')]);
        $token = $broker->createToken($user);

        Mail::to($user->email)->send(new APIUSerPasswordResetToken($token));
        return response()->json(['msg'=>"Σας αποστείλαμε το token δια μέσω email"],202);
    }


}

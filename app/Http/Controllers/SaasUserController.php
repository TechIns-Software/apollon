<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            new JsonResponse(['msg'=>'Το email και το password δεν έχουν δωθεί'],400);
        }

        if(auth()->guard('mobile_api_basic')->attempt(['email'=>$email,'password'=>$password])){
            $user = auth()->guard('mobile_api_basic')->user();
            return new JsonResponse(['token'=>$user->createToken('auth_token')->plainTextToken],201);
        }

        return new JsonResponse(['msg'=>"Η πρόσβαση Δεν επιτρέπετε"],403);
    }
}

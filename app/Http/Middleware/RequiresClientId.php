<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresClientId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->route('id');
        if(empty($id)){
            return new JsonResponse(['msg'=>"Ο πελάτης Δεν υπάρχει"],404);
        }

        $client = Client::find($id);

        if(empty($client)){
            return new JsonResponse(['msg'=>"Ο πελάτης Δεν υπάρχει"],404);
        }

        $user = $request->user();

        if($client->business_id != $user->business_id){
            return new JsonResponse(['msg'=>"Aπαγορεύετε"],403);
        }

        $request->merge(['client' => $client]);

        return $next($request);
    }
}

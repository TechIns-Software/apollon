<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BusinessIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        // User Is authorized from another middleware
        if(empty($user)){
            return $next($request);
        }
        $business = Business::find($user->business_id);
        if(!$business->active){
            return new JsonResponse(['msg'=>"H εταιρεία δεν ειναι ενεργή"],401);
        }
        return $next($request);
    }
}

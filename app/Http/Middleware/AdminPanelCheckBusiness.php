<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPanelCheckBusiness
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $businessId = $request->route('id');
        if($businessId < 0){
            return $this->missingBusinessIdResponse($request);
        }

        $business = Business::find($businessId);

        if(empty($business)){
            return $this->missingBusinessIdResponse($request);
        }

        $request->attributes->set('business', $business);

        return $next($request);
    }

    private function missingBusinessIdResponse(Request $request): Response
    {
        if(!$request->isAjax()) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['msg'=>"Η εταιρεία δεν υπάρχει"],Response::HTTP_NOT_FOUND);
    }
}

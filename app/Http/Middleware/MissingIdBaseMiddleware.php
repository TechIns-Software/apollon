<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class MissingIdBaseMiddleware
{

    protected $notFoundMsg="";
    protected $mergeRequestKey="";

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, \Closure $next,?string $inputParam=null): Response
    {
        $inputParam=$inputParam??'id';
        $id = $request->route($inputParam);

        if(empty($id)){
            return new JsonResponse(['msg'=>$this->notFoundMsg],404);
        }

        $model = $this->getModel($id);

        if(empty($model)){
            return new JsonResponse(['msg'=>$this->notFoundMsg],404);
        }

        $user = $request->user();
        if($user->business_id != $model->business_id){
            return new JsonResponse(['msg'=>"Aπαγορεύετε"],403);
        }

        $this->mergeRequest($request,$model);
        return $next($request);
    }

    abstract protected function getModel(int $id);

    protected function mergeRequest(Request $request,Model $model){

        if(empty($this->mergeRequestKey)){
            return;
        }
        $request->merge([$this->mergeRequestKey=>$model]);
    }
}

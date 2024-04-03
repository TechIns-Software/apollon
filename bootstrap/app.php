<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

    })
    ->withExceptions(function (Exceptions $exceptions){
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'msg' => 'Unsupported Method'
                ], 501);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e,Request $request){
            if ($request->is('api/*')) {
                return response()->json([
                    'msg' => 'Unsupported Method'
                ], 405);
            }
        });

        $exceptions->render(function(\Illuminate\Validation\ValidationException $e, Request $request){
            if ($request->is('api/*')) {
                return new \Illuminate\Http\JsonResponse(['errors' => $e->errors()], 400);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e,Request $request) {
            if ($request->is('api/*')) {
                return new \Illuminate\Http\JsonResponse(['errors' => "Access Denied"], 401);
            }
        });

        $exceptions->render(function (\Exception $e,Request $request) {
            if ($request->is('api/*')) {
                $responseBody = [
                    "msg"=> "Προέκυψε ένα εσωτερικό σφάλμα",
                ];

                if(\Illuminate\Support\Facades\App::environment(['local','testing'])){
                    $responseBody['debug']=[
                        'exception_msg'=>$e->getMessage(),
                        'trace'=>$e->getTrace()
                    ];
                }

                return response()->json($responseBody, 500);
            }
        });

    })->create();

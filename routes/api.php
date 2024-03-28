<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::put('/token',[\App\Http\Controllers\API\SaasUserController::class,'login'])
        ->name('api.login');

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('/client')->group(function (){
        Route::post('/',[\App\Http\Controllers\API\ClientController::class,'create'])->name('client.create');
        Route::get('/',[\App\Http\Controllers\API\ClientController::class,'list']);

        Route::middleware(\App\Http\Middleware\RequiresClientId::class)->group(function (){
            Route::post('/{id}',[\App\Http\Controllers\API\ClientController::class,'create']);
            Route::get('/{id}',[\App\Http\Controllers\API\ClientController::class,'client']);
        });
    });
});


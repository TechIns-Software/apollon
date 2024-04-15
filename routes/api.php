<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::put('/token',[\App\Http\Controllers\API\SaasUserController::class,'login'])
        ->name('api.login');

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/token',[\App\Http\Controllers\API\SaasUserController::class,'resetToken']);
    Route::delete('/token',[\App\Http\Controllers\API\SaasUserController::class,'logout']);
    Route::post('/user/password',[\App\Http\Controllers\API\SaasUserController::class,'updatePassword']);

    Route::middleware(\App\Http\Middleware\BusinessIsActive::class)->group(function (){

        Route::prefix('/client')->group(function (){
            Route::post('/',[\App\Http\Controllers\API\ClientController::class,'create'])->name('client.create');
            Route::get('/',[\App\Http\Controllers\API\ClientController::class,'list']);

            Route::middleware(\App\Http\Middleware\RequiresClientId::class)->group(function (){
                Route::post('/{id}',[\App\Http\Controllers\API\ClientController::class,'edit']);
                Route::get('/{id}',[\App\Http\Controllers\API\ClientController::class,'client']);
                Route::delete('/{id}',[\App\Http\Controllers\API\ClientController::class,'delete']);
            });
        });

        Route::prefix('/order')->group(function (){
            Route::post('/',[\App\Http\Controllers\API\OrderController::class,'add']);
            Route::get('/',[\App\Http\Controllers\API\OrderController::class,'list']);

            Route::post('/{id}',[\App\Http\Controllers\API\OrderController::class,'edit']);
            Route::get('/{id}',[\App\Http\Controllers\API\OrderController::class,'order']);
            Route::delete('/{id}',[\App\Http\Controllers\API\OrderController::class,'delete']);

            Route::post('/{id}/products',[\App\Http\Controllers\API\OrderController::class,'addItemToOrder']);
            Route::delete('/{id}/product/{product_id}',[\App\Http\Controllers\API\OrderController::class,'removeOrderProduct']);
        });

        Route::prefix('/delivery')->group(function (){
            Route::post('/',[\App\Http\Controllers\API\DeliveryController::class,'add']);
            Route::get('/',[\App\Http\Controllers\API\DeliveryController::class,'list']);

            Route::post('/{id}',[\App\Http\Controllers\API\DeliveryController::class,'edit']);
            Route::get('/{id}',[\App\Http\Controllers\API\DeliveryController::class,'delivery']);
            Route::delete('/{id}',[\App\Http\Controllers\API\DeliveryController::class,'delete']);
            Route::post('/order/{id}',[\App\Http\Controllers\API\DeliveryController::class,'changeSequenceOfOrders']);
        });

        Route::get('/driver',[\App\Http\Controllers\API\DeliveryController::class,'driver']);
    });
});


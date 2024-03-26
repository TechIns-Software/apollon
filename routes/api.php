<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::put('/token',[\App\Http\Controllers\API\SaasUserController::class,'login'])
    ->name('api.login');

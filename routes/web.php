<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login',[\App\Http\Controllers\UserController::class,'login'])->name('login');
Route::post('/login',[\App\Http\Controllers\UserController::class,'loginFormSubmit'])->name('auth.login');
Route::get('/logout',[\App\Http\Controllers\UserController::class,'logout'])->name('auth.logout');

// There are applicable only for users
Route::view('/forgot-password',"user.forgotPasswordEmailEntry")->name('user.reset-password');

Route::post('/forgot-password',[\App\Http\Controllers\PasswordController::class,'userForgetPasswordEmail'])
    ->middleware('guest')->name('password.email');

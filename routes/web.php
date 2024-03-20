<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if(Auth::check()) {
        return redirect()->route('user.list');
    }

    return redirect()->route('login');
});

Route::get('/login',[\App\Http\Controllers\UserController::class,'login'])->name('login');
Route::post('/login',[\App\Http\Controllers\UserController::class,'loginFormSubmit'])->name('auth.login');
Route::get('/logout',[\App\Http\Controllers\UserController::class,'logout'])->name('auth.logout');

// There are applicable only for users
Route::view('/forgot-password',"user.forgotPasswordEmailEntry")->name('user.reset-password');

Route::post('/forgot-password',[\App\Http\Controllers\PasswordController::class,'userForgetPasswordEmail'])
    ->middleware('guest')->name('password.email');

Route::any('/profile',[\App\Http\Controllers\UserController::class,'profile'])
    ->name('user.profile')
    ->middleware("auth:web");


Route::middleware(['auth'])->group(function () {
    Route::prefix('/user')->group(function (){
        Route::get('/register',[\App\Http\Controllers\UserController::class,'register'])->name('user.register.view');
        Route::post('/register',[\App\Http\Controllers\UserController::class,'register'])
            ->name('user.register');

        Route::get('/edit',[\App\Http\Controllers\UserController::class,'editUSer'])->name('user.edit.view');
        Route::post('/edit',[\App\Http\Controllers\UserController::class,'editUSer'])
            ->name('user.edit');
    });
    Route::get('/users',[\App\Http\Controllers\UserController::class,'listUsers'])->name('user.list');
});

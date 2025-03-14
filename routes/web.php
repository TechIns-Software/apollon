<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if(Auth::check()) {
        return redirect()->route('user.list');
    }

    return redirect()->route('login');
});

Route::get('/login',[\App\Http\Controllers\Panel\UserController::class,'login'])->name('login');
Route::post('/login',[\App\Http\Controllers\Panel\UserController::class,'loginFormSubmit'])->name('auth.login');
Route::get('/logout',[\App\Http\Controllers\Panel\UserController::class,'logout'])->name('auth.logout');

// There are applicable only for users
Route::view('/forgot-password',"user.forgotPasswordEmailEntry")->name('user.reset-password');

Route::post('/forgot-password',[\App\Http\Controllers\Panel\PasswordController::class,'userForgetPasswordEmail'])
    ->middleware('guest')->name('password.email');

Route::get('/reset-password',[\App\Http\Controllers\Panel\PasswordController::class,'resetUserPassword'])
    ->name('password.reset');
Route::post('/reset-password',[\App\Http\Controllers\Panel\PasswordController::class,'resetUserPasswordAction'])
    ->name('password.reset.submit');


Route::post('/saasuser/forgot-password',[\App\Http\Controllers\SaasUserPasswordController::class,'userForgetPasswordEmail'])
    ->middleware('guest')->name('saasuser.password.email');

Route::get('/saasuser/reset-password',[\App\Http\Controllers\SaasUserPasswordController::class,'resetUserPassword'])
    ->name('saasuser.password.reset');
Route::post('/saasuser/reset-password',[\App\Http\Controllers\SaasUserPasswordController::class,'resetUserPasswordAction'])
    ->name('saasuser.password.reset.submit');

Route::get('/saasuser/msg',[\App\Http\Controllers\SaasUserPasswordController::class,'changeSuccessOrFailMsgPage'])
    ->name('saasuser.password.change.success_or_error');

Route::any('/profile',[\App\Http\Controllers\Panel\UserController::class,'profile'])
    ->name('user.profile')
    ->middleware("auth:web");



Route::middleware(['auth'])->group(function () {
    Route::prefix('/user')->group(function (){
        Route::get('/register',[\App\Http\Controllers\Panel\UserController::class,'register'])->name('user.register.view');
        Route::post('/register',[\App\Http\Controllers\Panel\UserController::class,'register'])
            ->name('user.register');

        Route::get('/edit',[\App\Http\Controllers\Panel\UserController::class,'editUSer'])->name('user.edit.view');
        Route::post('/edit',[\App\Http\Controllers\Panel\UserController::class,'editUSer'])
            ->name('user.edit');
    });
    Route::get('/users',[\App\Http\Controllers\Panel\UserController::class,'listUsers'])->name('user.list');


    Route::post('/business',[\App\Http\Controllers\Panel\BusinessController::class,'create'])->name('business.create');
    Route::post('/business/edit',[\App\Http\Controllers\Panel\BusinessController::class,'edit'])->name('business.edit');
    Route::get('/business',[\App\Http\Controllers\Panel\BusinessController::class,'list'])->name('business.list');
    Route::get('/business/stats',[\App\Http\Controllers\Panel\BusinessController::class,'businessStats'])->name('business.stats');

    Route::prefix("/business/{id}")->middleware([\App\Http\Middleware\AdminPanelCheckBusiness::class])->group(function (){
        Route::get('/',[\App\Http\Controllers\Panel\BusinessController::class,'get'])->name('business.info');
        Route::get('/order/stats',[\App\Http\Controllers\Panel\BusinessController::class,'orderStats'])->name("order.stats");
        Route::post('/saasuser',[\App\Http\Controllers\Panel\SaasUserController::class,'add'])->name('business.user.create');
    });


    Route::post('/product',[\App\Http\Controllers\Panel\ProductsController::class,'addProduct'])->name("product.add");
    Route::get('/products',[\App\Http\Controllers\Panel\ProductsController::class,'listProducts'])->name("products.fetch");
    Route::post('/product/edit',[\App\Http\Controllers\Panel\ProductsController::class,'editProducts'])->name("product.edit");

    Route::prefix("/saasuser")->group(function (){
        Route::get('/',[\App\Http\Controllers\Panel\SaasUserController::class,'list'])->name('business.user');
        Route::get("/{id}",[\App\Http\Controllers\Panel\SaasUserController::class,'userInfo'])->name("saasuser.info");
        Route::post('/{id}',[\App\Http\Controllers\Panel\SaasUserController::class,'edit'])->name('business.user.edit');
    });
});


<?php

namespace App\Providers;

use App\Models\SaasUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (Authenticatable $user, string $token) {
            $params=[ 'token'=>$token, 'email'=>$user->email ];
            if($user instanceof SaasUser){
                return route('saasuser.password.reset',$params);
            }
            return route('password.reset',$params);
        });
    }
}

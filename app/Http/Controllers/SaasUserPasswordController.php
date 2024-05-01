<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Panel\PasswordController;
use Illuminate\Support\Facades\Password;

class SaasUserPasswordController extends Panel\PasswordController
{
    protected string  $redirectTo = 'saasuser.password.change.success_or_error';

    protected function getBroker()
    {
        return Password::broker('saas_users');
    }

    protected function resetPasswordUrl():?string
    {
        return route('saasuser.password.reset.submit');
    }

    public function changeSuccessOrFailMsgPage()
    {
        return view('layout.fullpage');
    }
}

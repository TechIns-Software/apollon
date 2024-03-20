<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Customer;

use Carbon\Carbon;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Config;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{

    protected function getBroker()
    {
        return Password::broker('users');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function userForgetPasswordEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $this->getBroker()->sendResetLink(['email'=>$request->email]);
        return back()->with('message', 'Σας αποστείλαμε ένα email με τον σύνδεσμο ανάκτησης κωδικού');
    }

    public function resetUserPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email'
        ]);

        return view('user.passwordresetform',['token'=>$request->token,'email'=>$request->email]);
    }

    public function resetUserPasswordAction(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:4|confirmed',
            'email' => 'required|email'
        ]);

        $response = $this->getBroker()->reset(
           [
               'email'=>$request->email,
               'token'=>$request->token,
               'password'=>$request->password,
               'password_confirmation'=>$request->password_confirmation
           ],
            function ($user, $password) use ($request) {
                $user->password = Hash::make($password);
                $user->save();

                $table = Config::get('auth.passwords.users.table');

                DB::table($table)
                    ->where(['email'=>$request->email])
                    ->delete();
            }
        );

        if ($response === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('message', 'Ο κωδικός σας άλλαξε επιτυχώς!');
        }

        if($response == Password::INVALID_TOKEN){
            return redirect()->route('login')->withErrors(['error'=> 'Παρακαλώ εκκινήστε την διαδικασία ανακησησης κωδικού ξανά']);

        }
        return redirect()->back()->withErrors(['error' => 'Το κωδικός σας δεν μπορεί να αλλαχθεί τώρα. Προσπαθήστε ξανά αργότερα.']);
    }
}

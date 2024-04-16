<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordController extends Controller
{

    /**
     * What route to use in order to redirect upon password change submission.
     * @param string
     */
    protected string  $redirectTo = 'login';

    protected function getBroker()
    {
        return Password::broker('users');
    }

    protected function resetPasswordUrl():?string
    {
        return null;
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

        $params = ['token'=>$request->token,'email'=>$request->email];
        $url = $this->resetPasswordUrl();

        if(!empty($url)){
            $params['route']=$url;
        }
        return view('user.passwordresetform',$params);
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
            return redirect()->route($this->redirectTo)->with('message', 'Ο κωδικός σας άλλαξε επιτυχώς!');
        }

        if($response == Password::INVALID_TOKEN){
            return redirect()->route($this->redirectTo)->withErrors(['error'=> 'Παρακαλώ εκκινήστε την διαδικασία ανάκτησης κωδικού ξανά']);

        }
        return redirect()->back()->withErrors(['error' => 'Το κωδικός σας δεν μπορεί να αλλαχθεί τώρα. Προσπαθήστε ξανά αργότερα.']);
    }
}

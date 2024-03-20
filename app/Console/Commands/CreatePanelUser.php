<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreatePanelUser extends Command
{
    protected $signature = 'user:create {email : Email of the user} {password : Password of the user}';

    protected $description = 'Create a new user with email and password. The user will have Panel Access Only';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        if(User::where('email',$email)->exists()){
            $this->error('User '.$email." Already exists");
            return -1;
        }

        $user = new User();

        try{
            $user->email = $email;
        } catch (\InvalidArgumentException $in){
            $this->error($in->getMessage());
            return -1;
        }
        $user->password = Hash::make($password);

        $name = $this->ask('How the user is called? (Actual physical name)');
        $name = trim($name);

        if(empty($name)){
            $this->warn("No name has given. Setting email as user's name");
            $name = $email;
        }

        $user->name = $name;

        $user->save();

        $this->info("SAVED USER: ".$user->email);
    }
}

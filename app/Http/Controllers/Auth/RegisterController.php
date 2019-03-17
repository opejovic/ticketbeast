<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    public function register()
    {
        $invitation = Invitation::findByCode(request('code'));

        $user = User::create([
            'email'    => request('email'),
            'password' => Hash::make(request('password')),
            'code'     => request('code'),
        ]);

        $invitation->update(['user_id' => $user->id]);

        Auth::login($user);

        return redirect()->route('backstage.concerts.index');
    }
}

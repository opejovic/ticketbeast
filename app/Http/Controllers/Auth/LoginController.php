<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login()
    {
        if (! Auth::attempt(request(['email', 'password']))) {
            return redirect('/login')->withErrors([
                'email' => ['Your credentials do not match our database records.'],
            ]);
        }

        return redirect('/backstage/concerts');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }
}

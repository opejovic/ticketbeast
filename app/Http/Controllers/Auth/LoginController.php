<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    public function login()
    {
        if (! Auth::attempt(request(['email', 'password']))) {
            return redirect('/login')->withInput(request(['email']))->withErrors([
                'email' => ['Your credentials do not match our database records.'],
            ]);
        }

        return redirect('/backstage/concerts/new');
    }   

    public function logout()
    {
        Auth::logout();

        return redirect('/login');        
    }
}

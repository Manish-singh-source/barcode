<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AuthController extends Controller
{
    public function loginForm(): View
    {
        return view('auth.login');
    }

    public function registerForm(): View
    {
        return view('auth.register');
    }

    public function forgotForm(): View
    {
        return view('auth.forgot');
    }
}

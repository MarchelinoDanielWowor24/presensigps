<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class AuthController extends Controller
{
    public function proseslogin(Request $request)
    {
        //$pass = 123;
        //echo Hash::make($pass);
        if (Auth::guard('pegawai')->attempt(['nik' => $request->nik, 'password' => $request->password])) {
            return redirect('/dashboard');
        } else {
            return Redirect('/')->with(['warning' => 'NIP / Password Salah']);
        }
    }

    public function proseslogout()
    {
        if (Auth::guard('pegawai')->check()) {
            Auth::guard('pegawai')->logout();
            return redirect('/');
        }
    }

    public function proseslogoutadmin()
    {
        if (Auth::guard('user')->check()) {
            Auth::guard('user')->logout();
            return redirect('/panel');
        }
    }


    public function prosesloginadmin(Request $request)
    {
        //$pass = 123;
        //echo Hash::make($pass);
        if (Auth::guard('user')->attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect('/panel/dashboardadmin');
        } else {
            return Redirect('/panel')->with(['warning' => 'Username / Password salah']);
        }
    }
}

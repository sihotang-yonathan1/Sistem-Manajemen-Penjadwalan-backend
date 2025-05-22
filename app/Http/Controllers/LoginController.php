<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {

        $credentials = $request->validate([
            'username' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

 

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return response()->json([
                "message" => "success"
            ]);

        }

 

        return response()->json([
            "message" => "Error when auth"
        ]);

    }

    public function logout(Request $request)
    {

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            "message" => "Successfully logout"
        ]);
    }
}

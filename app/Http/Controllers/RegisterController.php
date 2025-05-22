<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register_user(Request $request){
        $request->validate([
            "username" => ["required"],
            "email" => ["required", "email"],
            "password" => ["required"]
        ]);

        $user = new User();
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->email = $request->email;
        $user->save();

        return response()->json([
            "message" => "Register success"
        ]);
        
    }
}

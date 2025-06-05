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
            "password" => ["required"],
            "role" => ["required", "in:admin,dosen,mahasiswa"] // Validasi role

        ]);

        $user = new User();
        $user->username = $request->username;
        $user->password = $request->password;
        $user->role = $request->role;
        $user->save();

        return response()->json([
            "message" => "Register success"
        ]);
        
    }
}

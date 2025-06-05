<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // Cari user berdasarkan username
        $user = User::where('username', $credentials['username'])->first();

        // Cek apakah user ada dan password cocok (plain text)
        if ($user && $user->password === $credentials['password']) {
            // Login manual tanpa hash
            Auth::login($user);
            $request->session()->regenerate();
            
            return response()->json([
                "message" => "success",
                "data" => [
                    "id" => (string)$user->id,
                    "username" => $user->username,
                    "role" => $user->role
                ]
            ], 200);
        }

        // Return error response dengan status 401
        return response()->json([
            "message" => "Error when auth",
            "error" => "Invalid username or password"
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            "message" => "Successfully logout"
        ], 200);
    }

    public function get_current_user(Request $request){
        $user = Auth::user();
        return response()->json([
            "message" => "ok",
            "data" => $user,
        ]);
    }
}

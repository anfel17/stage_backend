<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Etudiant;
use App\Models\Responsable;
use App\Models\Chef;
use Illuminate\Support\Str;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Attempt authentication for each user type
        if (Auth::guard('etudiant')->attempt($credentials)) {
            $user = Auth::guard('etudiant')->user();
            if ($user) {
                $token = $user->createToken('Token Name')->accessToken;
                return response()->json([
                    'user_type'=>'student',
                    'status' => 'success',
                    'user' => $user,
                    'authorisation' => [
                        'token' => $token,
                    ]
                ]);
            }
        } else if (Auth::guard('responsable')->attempt($credentials)) {
            $user = Auth::guard('responsable')->user();
            if ($user) {
                $token = $user->createToken('Token Name')->accessToken;
                return response()->json([
                    'user_type'=>'supervisor',
                    'status' => 'success',
                    'user' => $user,
                    'authorisation' => [
                        'token' => $token,
                        
                    ]
                ]);
            }
        } else if (Auth::guard('chefdepartement')->attempt($credentials)) {
            $user = Auth::guard('chefdepartement')->user();
            if ($user) {
                $token = $user->createToken('Token Name')->accessToken;
                return response()->json([
                    'user_type'=>'admin',
                    'status' => 'success',
                    'user' => $user,
                    'authorisation' => [
                        'token' => $token,
                        'type' => 'bearer',
                    ]
                ]);
            }
        } else {
            return "Authentication failed for all user types";
        }
    }
    public function logout(Request $request)
    {
        $guards = array_keys(config('auth.guards'));
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                $user->token()->revoke();
            }
        }
        return response()->json(['message' => 'Successfully logged out']);
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Etudiant;
use App\Models\Responsable;
use App\Models\Chef;


class LoginController extends Controller
{
   
    //    return  $password = Hash::make('0000');
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        // Attempt authentication for each user type
        if (Auth::guard('etudiant')->attempt($credentials)) {
            return "Authentication passed for Etudiant";  
        }
         else if (Auth::guard('responsable')->attempt($credentials)) {
            return "Authentication passed for Responsable"; 
        } 
        else if (Auth::guard('chefdepartement')->attempt($credentials)) {
            return "Authentication passed for chef"; 
        } else {
            return "Authentication failed for all user types"; 
        }
    }
    
}

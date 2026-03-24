<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
 /**
 * Handle an authentication attempt.
 */
public function authenticate(Request $request)
{  
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
  
        $user = Auth::user();
        
        if ($user->email_verified_at === null) {
            // L'authentification a réussi mais l'email n'est pas vérifié
            return response()->json([
                'errors' => [
                    'email' => ['Veuillez vérifier votre adresse email avant de vous connecter.']
                ]
            ], 403);
        }
        
        // L'email est vérifié, on régénère la session et on continue
        $request->session()->regenerate();

        return response()->json(['user' => $user]);
    }

    return response()->json([
        'errors' => [
            'credentials' => ['Les identifiants sont incorrects.']
        ]
    ], 401);
}

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
    return response()->json(['success' => true]);
    }
}

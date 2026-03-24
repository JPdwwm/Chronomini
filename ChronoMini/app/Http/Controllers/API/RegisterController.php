<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Mail\RegisterEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Validation des champs du formulaire
        $formFields = $request->validate([
            'email' => [
            'required',
            'email:strict,dns',
            'unique:users,email',
            ],
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'password' => ['required', 'confirmed', Password::default()],
            'role_id' => 'required|integer|in:2,3', // in 2:3 signifie que seulement les rôles parent et asmat peuvent être envoyé dans la requête de création 
            'city' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:10'
        ]);

        $user = new User();
        $user->fill($formFields);
        $user->password = Hash::make($formFields['password']);
        $user->token = Str::random(40);
        $user->save();

        Mail::to($user->email)->send(new RegisterEmail($user));

        return response()->json([
            'User' => $user,
            'Message' => 'User successfully created'
        ], 201); 
    }

    public function verification(Request $request)
    {
        $formFields = $request->validate([
            'email' => 'required|string|email|exists:users',
            'token' => 'required|string|min:40|exists:users'
        ]);
    
        // D'abord, vérifier si l'utilisateur existe avec ces identifiants
        $user = User::where('email', '=', $formFields['email'])
            ->where('token', '=', $formFields['token'])
            ->first();
    
        if (!$user) {
            return response()->json(['error' => 'Invalid verification parameters'], 400);
        }
        
        // Ensuite, vérifier si l'email est déjà vérifié
        if ($user->email_verified_at !== null) {
            return response()->json(['message' => 'Email already verified', 'alreadyVerified' => true], 200);
        }
        
        // Marquer l'email comme vérifié et supprimer le token (usage unique)
        $user->email_verified_at = now();
        $user->token = null;
        $user->save();
        
        return response()->json(['message' => 'Email verified successfully'], 200);
    }
}


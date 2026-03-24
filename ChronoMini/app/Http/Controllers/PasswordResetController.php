<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Mail\PasswordResetEmail;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    // Étape 1: Demande de réinitialisation
    public function requestReset(Request $request)
    {
        $formFields = $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // Supprimer tout token existant pour cet email
        DB::table('password_reset_tokens')
            ->where('email', $formFields['email'])
            ->delete();
        
        // Générer un token unique
        $resetToken = Str::random(40);
        
        // Insérer dans la table password_reset_tokens
        DB::table('password_reset_tokens')->insert([
            'email' => $formFields['email'],
            'token' => $resetToken,
            'created_at' => Carbon::now()
        ]);
        
        // Récupérer l'utilisateur pour envoyer l'email
        $user = User::where('email', $formFields['email'])->first();
        $user->reset_token = $resetToken; // Temporairement stocké pour l'email
        
        // Envoyer l'email avec le lien de réinitialisation
        Mail::to($user->email)->send(new PasswordResetEmail($user));
        
        return response()->json([
            'message' => 'Email de réinitialisation envoyé avec succès'
        ], 200);
    }
    
    // Étape 2: Vérifier le token
    public function verifyToken(Request $request)
    {
        $formFields = $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string|min:40'
        ]);
        
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $formFields['email'])
            ->where('token', $formFields['token'])
            ->first();
            
        if (!$resetRecord) {
            return response()->json(['error' => 'Token invalide'], 400);
        }
        
        // Vérifier si le token a expiré (plus de 24h)
        $tokenCreatedAt = Carbon::parse($resetRecord->created_at);
        if (Carbon::now()->diffInHours($tokenCreatedAt) > 24) {
            return response()->json(['error' => 'Token expiré'], 400);
        }
        
        return response()->json(['message' => 'Token valide'], 200);
    }
    
    // Étape 3: Réinitialiser le mot de passe
    public function resetPassword(Request $request)
    {
        $formFields = $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string|min:40',
            'password' => ['required', 'confirmed', Password::default()]
        ]);
        
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $formFields['email'])
            ->where('token', $formFields['token'])
            ->first();
            
        if (!$resetRecord) {
            return response()->json(['error' => 'Token invalide'], 400);
        }
        
        // Vérifier si le token a expiré (plus de 24h)
        $tokenCreatedAt = Carbon::parse($resetRecord->created_at);
        if (Carbon::now()->diffInHours($tokenCreatedAt) > 24) {
            return response()->json(['error' => 'Token expiré'], 400);
        }
        
        // Mettre à jour le mot de passe de l'utilisateur
        $user = User::where('email', $formFields['email'])->first();
        $user->password = Hash::make($formFields['password']);
        $user->save();
        
        // Supprimer le token utilisé
        DB::table('password_reset_tokens')
            ->where('email', $formFields['email'])
            ->delete();
        
        return response()->json(['message' => 'Mot de passe réinitialisé avec succès'], 200);
    }
}
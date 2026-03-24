<?php

namespace App\Http\Controllers\API;


use App\Models\User;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ConnectionRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function profile()
    {
        // Récupère l'utilisateur authentifié
        $user = Auth::user();

        // Retourne les informations de l'utilisateur en format JSON
        return response()->json($user);
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
    
        // Vérifier l'autorisation avant de mettre à jour
        $this->authorize('updateMe', [$user, $user]);
    
        // Validation des données fournies dans la requête
        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:50',
            'last_name' => 'sometimes|string|max:50',
            'email' => [
            'sometimes', 
            'email:strict,dns', 
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => ['sometimes', 'confirmed', Password::default()],
            'city' => 'sometimes|nullable|string|max:50',
            'zip_code' => 'sometimes|nullable|string|max:5',
        ]);
    
        // Mise à jour des champs du modèle utilisateur avec les données validées
        $user->fill($validatedData);
    
        // Hash le mot de passe si un nouveau mdp est fourni dans la requête
        if ($request->has('password')) {
            $user->password = Hash::make($request->input('password'));
        }
    
        // Sauvegarder les changements dans la base de données
        $user->save();
    
        // Retourer une réponse JSON indiquant que la mise à jour a réussi, avec les données mises à jour
        return response()->json([
            'message' => 'Profile mis à jour avec succès',
            'user' => $user // Retourne l'utilisateur mis à jour
        ], 200);
    }
    
    public function destroy()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
    
        // Vérifier l'autorisation avant de supprimer
        $this->authorize('delete', $user);
        
        // Commencer une transaction pour assurer l'intégrité des données
        DB::beginTransaction();
        
        try {
            // Pour chaque enfant lié à cet utilisateur
            foreach ($user->kids as $kid) {
                // Détacher l'enfant de l'utilisateur
                $user->kids()->detach($kid->id);
                
                // Vérifier si l'enfant est encore lié à d'autres utilisateurs
                $remainingAssociations = $kid->users()->count();
                
                // Si aucun autre utilisateur n'est lié à cet enfant, supprimer tous ses relevés et l'enfant lui-même
                if ($remainingAssociations === 0) {
                    Record::where('kid_id', $kid->id)->delete();
                    $kid->delete();
                }
            }
            
            // Supprimer les connexions avec d'autres utilisateurs en les marquant comme disconnected
            ConnectionRequest::where(function($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })->update(['status' => 'disconnected']);
            
            // Mettre à jour le statut des demandes de connexion
            ConnectionRequest::where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id)
                ->update(['status' => 'disconnected']);
            
            // supprimer le compte utilisateur
            $user->delete();
            
            DB::commit();
            
            return response()->json(['message' => 'Votre compte a été supprimé avec succès.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression compte utilisateur #' . $user->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la suppression du compte.'], 500);
        }
    }
}

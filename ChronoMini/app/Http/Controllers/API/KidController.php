<?php

namespace App\Http\Controllers\API;

use App\Models\Kid;
use App\Models\User;
use App\Models\Record;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class KidController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Vérifer que l'utilisateur est autorisé à voir la liste de tous les enfants
        $this->authorize('viewAllKids', Kid::class);

        // On récupère tous les enfanrts
        $kids = Kid::all();
                
        // On retourne les informations des utilisateurs en format JSON
        return response()->json($kids);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createKid(Request $request)
    {
        // Get authenticated user
        $id = Auth::user()->id;
        $user = User::find($id);

        // Validation des données d'entrée
        $validatedData = $request->validate([
        'first_name' => 'required|string|max:50',
        'birth_date' => 'required|date',
        ]);

        // Créer un nouvel enfant avec les données validées
        $kid = Kid::create($validatedData);

        // Associer l'enfant à l'utilisateur authentifié
        $user->kids()->attach($kid->id);

        return response()->json($kid, 201); // 201 Created
    }

    public function showOneKid(Kid $kid)
    {

        $this->authorize('showOneKid', $kid, Kid::class);

        if (!$kid) {
            return response()->json(['message' => 'Enfant non trouvé'], 404);
        }

        return response()->json($kid, 200);
    }

    /**
     * Display the specified resource.
     */
    public function showMyKids()
    {
        $user = Auth::user();

        if ($user->kids->isEmpty()) {
            return response()->json(['message' => 'Aucun enfant trouvé.'],200); 
        }
        return response()->json($user->kids); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateKid(Request $request, Kid $kid)
    {
        // policy vérifiant que l'utilisateur est authentifié et qu'il est bien lié a l'enfant 
        $this->authorize('updateKid', $kid, Kid::class);

        $validatedData = $request->validate([
            'first_name' => 'sometimes|required|string|max:50',
            'birth_date' => 'sometimes|required|date',
        ]);

        $kid->update($validatedData);

        return response()->json([
            'message' => 'Mise à jour réussie',
            'kid' => $kid
        ], 200);
    }

/**
 * Remove the specified resource from storage.
 */
    public function deleteKid(Kid $kid)
    {
        $this->authorize('deleteKid', $kid);

        $id = Auth::user()->id;
        $user = User::find($id);

        // Détacher l'enfant de l'utilisateur
        $user->kids()->detach($kid->id);
        
        // Vérifie si l'enfant est toujours rattaché à d'autres utilisateurs
        $remainingAssociations = $kid->users()->count();
        
        // S'il n'y a plus d'associations, on peut supprimer l'enfant de la base de données
        if ($remainingAssociations === 0) {
         // Supprimer d'abord tous les enregistrements (records) liés à cet enfant
        Record::where('kid_id', $kid->id)->delete();
        
        // Ensuite supprimer l'enfant
            $kid->delete();
        return response()->json(['message' => 'Enfant supprimé avec succès.'], 200);
        } else {
            return response()->json(['message' => 'Enfant détaché avec succès.'], 200);
        }
    }
}

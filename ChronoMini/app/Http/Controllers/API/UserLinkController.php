<?php

namespace App\Http\Controllers\API;

use App\Models\Kid;
use App\Models\User;
use App\Models\Record;
use Illuminate\Http\Request;
use App\Models\ConnectionRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserLinkController extends Controller
{

/**
 * Envoyer une demande de connexion via le code de liaison
 */
public function sendConnectionRequest(Request $request)
{
    // Valider le code de liaison
    $request->validate([
        'link_code' => 'required|string|exists:users,link_code'
    ]);

    /** @var \App\Models\User $currentUser */
    $currentUser = Auth::user();
    
    // Trouver l'utilisateur à qui envoyer la demande
    $receiverUser = User::where('link_code', $request->link_code)->first();
    
    // Vérifier que ce n'est pas le même utilisateur
    if ($currentUser->id === $receiverUser->id) {
        return response()->json(['message' => 'Vous ne pouvez pas vous connecter à vous-même.'], 400);
    }
    
    // Vérifier que les rôles sont compatibles
    $isValidConnection = 
        ($currentUser->isParent() && $receiverUser->isAsmat()) || 
        ($currentUser->isAsmat() && $receiverUser->isParent()) ||
        ($currentUser->isParent() && $receiverUser->isParent());
    
    if (!$isValidConnection) {
        return response()->json(['message' => 'Connexion impossible: les rôles doivent être compatibles.'], 400);
    }

    // Rechercher toutes les demandes entre ces utilisateurs
    $existingRequests = ConnectionRequest::where(function($query) use ($currentUser, $receiverUser) {
        $query->where(function($q) use ($currentUser, $receiverUser) {
            $q->where('sender_id', $currentUser->id)
              ->where('receiver_id', $receiverUser->id);
        })->orWhere(function($q) use ($currentUser, $receiverUser) {
            $q->where('sender_id', $receiverUser->id)
              ->where('receiver_id', $currentUser->id);
        });
    })->get();
    
    // Vérifier s'il y a des demandes actives
    $activeRequest = $existingRequests->first(function($request) {
        return in_array($request->status, ['pending', 'accepted']);
    });
    
    if ($activeRequest) {
        return response()->json(['message' => 'Une demande de connexion active existe déjà entre ces utilisateurs.'], 400);
    }
    
    // S'il y a une demande déconnectée, la supprimer pour éviter les conflits de clé unique
    foreach ($existingRequests as $req) {
        $req->delete();
    }
    
    // Créer une nouvelle demande de connexion
    $connectionRequest = new ConnectionRequest();
    $connectionRequest->sender_id = $currentUser->id;
    $connectionRequest->receiver_id = $receiverUser->id;
    $connectionRequest->status = 'pending';
    $connectionRequest->save();
    
    return response()->json([
        'message' => 'Demande de connexion envoyée avec succès.',
        'request' => $connectionRequest,
        'receiver' => [
            'id' => $receiverUser->id,
            'name' => $receiverUser->first_name . ' ' . $receiverUser->last_name,
            'role' => $receiverUser->role->name
        ]
    ], 201);
}

/**
 * Récupérer les demandes de connexion reçues
 */
public function getReceivedRequests()
{
    /** @var \App\Models\User $currentUser */
    $currentUser = Auth::user();
    
    $requests = $currentUser->receivedConnectionRequests()
        ->with('sender:id,first_name,last_name,email,role_id')
        ->with('sender.role')
        ->where('status', 'pending')
        ->get();
    
    return response()->json(['requests' => $requests]);
}

/**
 * Récupérer les demandes de connexion envoyées
 */
public function getSentRequests()
{
    /** @var \App\Models\User $currentUser */
    $currentUser = Auth::user();
    
    $requests = $currentUser->sentConnectionRequests()
        ->with('receiver:id,first_name,last_name,email,role_id')
        ->with('receiver.role')
        ->get();
    
    return response()->json(['requests' => $requests]);
}

/**
 * Accepter une demande de connexion
 */
public function acceptRequest($requestId)
{
    /** @var \App\Models\User $currentUser */
    $currentUser = Auth::user();
    
    $request = ConnectionRequest::where('id', $requestId)
        ->where('receiver_id', $currentUser->id)
        ->where('status', 'pending')
        ->firstOrFail();
    
    $senderUser = User::findOrFail($request->sender_id);
    
    // Mise à jour du statut de la demande
    $request->status = 'accepted';
    $request->save();
    
    // Identifier les enfants potentiellement dupliqués
    $potentialDuplicates = $this->findPotentialDuplicates($currentUser, $senderUser);
    
    return response()->json([
        'message' => 'Demande de connexion acceptée avec succès.',
        'partner' => [
            'id' => $senderUser->id,
            'name' => $senderUser->first_name . ' ' . $senderUser->last_name,
            'role' => $senderUser->role->name
        ],
        'potential_duplicates' => $potentialDuplicates
    ]);
}

/**
 * Refuser une demande de connexion
 */
public function declineRequest($requestId)
{
    /** @var \App\Models\User $currentUser */
    $currentUser = Auth::user();
    
    $request = ConnectionRequest::where('id', $requestId)
        ->where('receiver_id', $currentUser->id)
        ->where('status', 'pending')
        ->firstOrFail();
    
    // Mise à jour du statut de la demande
    $request->status = 'declined';
    $request->save();
    
    return response()->json([
        'message' => 'Demande de connexion refusée.'
    ]);
}
    
    /**
     * Trouve les enfants potentiellement dupliqués entre deux utilisateurs
     */
    private function findPotentialDuplicates($user1, $user2)
    {
        $potentialDuplicates = [];
        
        $user1Kids = $user1->kids;
        $user2Kids = $user2->kids;
        
        foreach ($user1Kids as $kid1) {
            foreach ($user2Kids as $kid2) {
                // Comparer les prénoms et dates de naissance
                if (strtolower($kid1->first_name) === strtolower($kid2->first_name) &&
                    $kid1->birth_date === $kid2->birth_date) {
                    $potentialDuplicates[] = [
                        'kid1' => [
                            'id' => $kid1->id,
                            'first_name' => $kid1->first_name,
                            'birth_date' => $kid1->birth_date
                        ],
                        'kid2' => [
                            'id' => $kid2->id,
                            'first_name' => $kid2->first_name,
                            'birth_date' => $kid2->birth_date
                        ]
                    ];
                }
            }
        }
        
        return $potentialDuplicates;
    }
    
    public function mergeKids(Request $request)
    {
        $request->validate([
            'kid_to_keep' => 'required|integer|exists:kids,id',
            'kid_to_remove' => 'required|integer|exists:kids,id|different:kid_to_keep'
        ], [
            'kid_to_remove.different' => 'Impossible de fusionner un enfant avec lui-même.'
        ]);
        
        $kidToKeep = Kid::findOrFail($request->kid_to_keep);
        $kidToRemove = Kid::findOrFail($request->kid_to_remove);
        
        // L'utilisateur actuel
        $currentUser = Auth::user();
        
        // Vérifier si l'utilisateur a accès à au moins l'un des enfants
        $hasAccessToKidToKeep = $currentUser->kids->contains($kidToKeep);
        $hasAccessToKidToRemove = $currentUser->kids->contains($kidToRemove);
        
        if (!$hasAccessToKidToKeep && !$hasAccessToKidToRemove) {
            return response()->json(['message' => 'Vous n\'avez pas accès à ces enfants.'], 403);
        }
        
        // Déterminer quel enfant a l'historique le plus ancien
        $oldestKid = $this->getKidWithOldestHistory($kidToKeep, $kidToRemove);
        
        // Si l'enfant avec l'historique le plus ancien n'est pas celui choisi pour être conservé,
        // nous inversons les enfants
        if ($oldestKid->id !== $kidToKeep->id) {
            $temp = $kidToKeep;
            $kidToKeep = $kidToRemove;
            $kidToRemove = $temp;
        }
        
        DB::beginTransaction();
        
        try {
            // Récupérer tous les utilisateurs liés à l'enfant à supprimer
            $usersToLink = $kidToRemove->users()->get();
            
            // les lier à l'enfant à conserver
            foreach ($usersToLink as $user) {
                // Éviter les doublons
                if (!$kidToKeep->users->contains($user->id)) {
                    $kidToKeep->users()->attach($user->id);
                }
            }
            
            // Supprimer d'abord les enregistrements liés à l'enfant à supprimer
            DB::table('records')->where('kid_id', $kidToRemove->id)->delete();
            
            // Supprimer l'enfant à retirer
            $kidToRemove->users()->detach();
            $kidToRemove->delete();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Enfants fusionnés avec succès. Les relevés de l\'enfant le plus récent ont été supprimés.',
                'kid' => $kidToKeep
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur fusion enfants (keep: ' . $request->kid_to_keep . ', remove: ' . $request->kid_to_remove . '): ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la fusion.'], 500);
        }
    }

    private function getKidWithOldestHistory($kid1, $kid2) 
    {
        $firstRecord1 = Record::where('kid_id', $kid1->id)->orderBy('created_at')->first();
        $firstRecord2 = Record::where('kid_id', $kid2->id)->orderBy('created_at')->first();
        
        // Si un enfant n'a pas de records, l'autre est considéré comme ayant l'historique le plus ancien
        if (!$firstRecord1 && $firstRecord2) return $kid2;
        if ($firstRecord1 && !$firstRecord2) return $kid1;
        if (!$firstRecord1 && !$firstRecord2) return $kid1; // Par défaut si aucun n'a de records
        
        // Comparer les dates du premier record
        return ($firstRecord1->created_at < $firstRecord2->created_at) ? $kid1 : $kid2;
    }
    
    /**
     * Partager un enfant avec un autre utilisateur
     */
    public function shareKid(Request $request)
    {
        $request->validate([
            'kid_id' => 'required|integer|exists:kids,id',
            'user_id' => 'required|integer|exists:users,id'
        ]);
        
        $currentUser = Auth::user();
        $kidToShare = Kid::findOrFail($request->kid_id);
        $userToShareWith = User::findOrFail($request->user_id);
        
        // Vérifier que l'utilisateur actuel a accès à cet enfant
        if (!$currentUser->kids->contains($kidToShare)) {
            return response()->json(['message' => 'Vous n\'avez pas accès à cet enfant.'], 403);
        }
        
        // Vérifier que l'utilisateur cible n'a pas déjà accès à cet enfant
        if ($userToShareWith->kids->contains($kidToShare)) {
            return response()->json(['message' => 'Cet utilisateur a déjà accès à cet enfant.'], 400);
        }
        
        // Partager l'enfant
        $userToShareWith->kids()->attach($kidToShare->id);
        
        return response()->json([
            'message' => 'Enfant partagé avec succès.',
            'kid' => $kidToShare,
            'shared_with' => [
                'id' => $userToShareWith->id,
                'name' => $userToShareWith->first_name . ' ' . $userToShareWith->last_name
            ]
        ], 200);
    }

    public function disconnectUsers(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id'
        ]);
    
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        $partnerUser = User::findOrFail($request->user_id);
    
        // Vérifier si l'un des utilisateurs est une assistante maternelle
        $asmatUser = $currentUser->isAsmat() ? $currentUser : ($partnerUser->isAsmat() ? $partnerUser : null);
        $parentUser = $currentUser->isParent() ? $currentUser : ($partnerUser->isParent() ? $partnerUser : null);
    
        if ($asmatUser && $parentUser) {
            // Récupérer les enfants partagés entre ces utilisateurs
            $sharedKids = $asmatUser->kids()
                ->whereIn('kids.id', $parentUser->kids()->pluck('kids.id'))
                ->get();
        
            // Modifier l'accès pour l'assistante maternelle
            foreach ($sharedKids as $kid) {
                DB::table('kid_user')
                    ->where('kid_id', $kid->id)
                    ->where('user_id', $asmatUser->id)
                    ->update([
                        'access_type' => 'readonly',
                        'end_date' => now() // Date de séparation
                    ]);
            }
        }
    
        // Mettre à jour le statut des demandes de connexion à disconnected
        ConnectionRequest::where(function($query) use ($currentUser, $partnerUser) {
            $query->where('sender_id', $currentUser->id)
                  ->where('receiver_id', $partnerUser->id);
        })->orWhere(function($query) use ($currentUser, $partnerUser) {
            $query->where('sender_id', $partnerUser->id)
                  ->where('receiver_id', $currentUser->id);
        })->update(['status' => 'disconnected']);
    
        return response()->json(['message' => 'Utilisateurs déconnectés avec succès.']);
    }

    public function getConnectedUsers()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        
        // Récupérer les demandes acceptées
        $acceptedRequests = ConnectionRequest::where('status', 'accepted')
            ->where(function($query) use ($currentUser) {
                $query->where('sender_id', $currentUser->id)
                      ->orWhere('receiver_id', $currentUser->id);
            })->get();
        
        // Extraire les IDs des utilisateurs connectés
        $connectedUserIds = $acceptedRequests->map(function($request) use ($currentUser) {
            return $request->sender_id == $currentUser->id ? $request->receiver_id : $request->sender_id;
        });
        
        // Récupérer les utilisateurs avec leur rôle
        $connectedUsers = User::whereIn('id', $connectedUserIds)
            ->with('role')
            ->get();
        
        return response()->json([
            'connected_users' => $connectedUsers
        ]);
    }

    /**
    * Récupérer les enfants potentiellement dupliqués avec un utilisateur connecté
    */
    public function checkDuplicatesWithUser($userId)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
    
        // Vérifier que l'utilisateur cible est bien connecté avec l'utilisateur actuel
        $isConnected = ConnectionRequest::where('status', 'accepted')
            ->where(function($query) use ($currentUser, $userId) {
                $query->where(function($q) use ($currentUser, $userId) {
                    $q->where('sender_id', $currentUser->id)
                      ->where('receiver_id', $userId);
                })->orWhere(function($q) use ($currentUser, $userId) {
                    $q->where('sender_id', $userId)
                      ->where('receiver_id', $currentUser->id);
                });
            })->exists();
        
        if (!$isConnected) {
            return response()->json(['message' => 'Cet utilisateur n\'est pas connecté avec vous.'], 400);
        }
    
        $partnerUser = User::findOrFail($userId);
    
        // Trouver les doublons potentiels entre les deux utilisateurs
        $potentialDuplicates = $this->findPotentialDuplicates($currentUser, $partnerUser);
    
        return response()->json([
            'potential_duplicates' => $potentialDuplicates
        ]);
    }

    /**
    * Partager un enfant avec un partenaire connecté
    */
    public function shareKidWithPartner(Request $request)
    {
        $request->validate([
            'kid_id' => 'required|integer|exists:kids,id',
            'partner_id' => 'required|integer|exists:users,id'
        ]);
    
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
    
        // Vérifier que le partenaire est bien connecté avec l'utilisateur actuel
        $isConnected = ConnectionRequest::where('status', 'accepted')
            ->where(function($query) use ($currentUser, $request) {
                $query->where(function($q) use ($currentUser, $request) {
                    $q->where('sender_id', $currentUser->id)
                      ->where('receiver_id', $request->partner_id);
                })->orWhere(function($q) use ($currentUser, $request) {
                    $q->where('sender_id', $request->partner_id)
                      ->where('receiver_id', $currentUser->id);
                });
            })->exists();
        
        if (!$isConnected) {
            return response()->json(['message' => 'Cet utilisateur n\'est pas connecté avec vous.'], 400);
        }
    
        $kidToShare = Kid::findOrFail($request->kid_id);
        $partnerUser = User::findOrFail($request->partner_id);
    
        // Vérifier que l'utilisateur actuel a accès à cet enfant
        if (!$currentUser->kids->contains($kidToShare)) {
            return response()->json(['message' => 'Vous n\'avez pas accès à cet enfant.'], 403);
        }
    
        // Vérifier que le partenaire n'a pas déjà accès à cet enfant
        if ($partnerUser->kids->contains($kidToShare)) {
            return response()->json(['message' => 'Ce partenaire a déjà accès à cet enfant.'], 400);
        }
    
        // Partager l'enfant
        $partnerUser->kids()->attach($kidToShare->id);
    
        return response()->json([
            'message' => 'Enfant partagé avec succès.',
            'kid' => $kidToShare
        ], 200);
    }
}
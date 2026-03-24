<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{

    use HandlesAuthorization;
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function update(?User $user)
    {
        if ($user->isAdmin()){
            return true;
        }
        return false;
    }

    public function viewAll(?User $user)
    {
        if ($user->isAdmin()){
            return true;
        }
        return false;
    }

    public function viewOne(?User $user)
    {
        if ($user->isAdmin()){
            return true;
        }
        return false;
    }

    public function delete(User $authUser, User $user)
    {
        // Si l'utilisateur à supprimer est un admin, interdire la suppression
        if ($user->isAdmin()) {
            return false;
        }
        
        // Sinon, permettre la suppression si l'utilisateur authentifié est admin ou s'il s'agit de son propre compte
        return $authUser->isAdmin() || $authUser->id === $user->id;
    }

    /**
     * Autorisation pour un utilisateur de mettre à jour son propre profil.
     */
    public function updateMe(User $authUser, User $targetUser)
    {
        return $authUser->id === $targetUser->id; 
    }


}

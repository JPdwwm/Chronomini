<?php

namespace App\Policies;

use App\Models\Kid;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class KidPolicy
{

    public function viewAllKids(User $user)
    {
        //seuls les administrateurs peuvent voir tous les enfants
        return $user->isAdmin();
    }

    public function showOneKid(User $user, Kid $kid)
    {
        // Un utilisateur peut voir un enfant s'il est lié à cet enfant (via la table pivot)
        return $user->kids()->where('kid_id', $kid->id)->exists() || $user->isAdmin();
    }
    
    public function updateKid(User $user, Kid $kid)
    {
        // Si c'est un parent
        if ($user->isParent() && $user->kids()->where('kids.id', $kid->id)->exists()) {
            return true;
        }
        
        // Si c'est une assistante maternelle
        if ($user->isAsmat()) {
            // Vérifier que l'assistante maternelle a un accès complet à l'enfant
            $access = DB::table('kid_user')
                ->where('kid_id', $kid->id)
                ->where('user_id', $user->id)
                ->first();
                
            return $access && $access->access_type === 'full';
        }
        
        return false;
    }

    public function detachKid(User $user, Kid $kid)
    {
        // Un utilisateur peut détacher un enfant s'il est lié à cet enfant
        return $user->kids()->where('kid_id', $kid->id)->exists();
    }

    public function deleteKid(User $user, Kid $kid)
    {
        // Si c'est un parent
        if ($user->isParent() && $user->kids()->where('kids.id', $kid->id)->exists()) {
            return true;
        }
        
        // Si c'est une assistante maternelle
        if ($user->isAsmat()) {
            // Vérifier que l'assistante maternelle a un accès complet à l'enfant
            $access = DB::table('kid_user')
                ->where('kid_id', $kid->id)
                ->where('user_id', $user->id)
                ->first();
                
            return $access && $access->access_type === 'full';
        }
        
        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\Kid;
use App\Models\User;
use App\Models\Record;
use Illuminate\Support\Facades\DB;

class RecordPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAll(User $user)
    {
        return $user->isAdmin();
    }

    public function showOneRecord(User $user, Record $record)
    {
        // Récupérer l'accès de l'utilisateur à l'enfant
        $access = DB::table('kid_user')
            ->where('kid_id', $record->kid_id)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$access) {
            return false;
        }
        
        // Si l'accès est en lecture seule avec une date de fin
        if ($access->access_type === 'readonly' && $access->end_date) {
            // Vérifier que le record a été créé avant la date de fin
            return $record->created_at->lte($access->end_date);
        }
        
        return true;
    }

    public function startRecording(User $user, Kid $kid)
    {
        // Récupérer l'accès de l'utilisateur à l'enfant
        $access = DB::table('kid_user')
            ->where('kid_id', $kid->id)
            ->where('user_id', $user->id)
            ->first();
            
        // Seulement les utilisateurs avec accès complet peuvent démarrer un enregistrement
        if (!$access || $access->access_type !== 'full') {
            return false;
        }
        
        // Vérifier si l'utilisateur est un parent ou un assistant maternel
        return $user->isParent() || $user->isAsmat();
    }

    public function stopRecording(User $user, Kid $kid)
    {

        $access = DB::table('kid_user')
            ->where('kid_id', $kid->id)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$access || $access->access_type !== 'full') {
            return false;
        }
        
        return $user->isParent() || $user->isAsmat();
    }

    public function viewKidRecords(User $user, Kid $kid)
        {
        // Récupérer l'accès de l'utilisateur à l'enfant
        $access = DB::table('kid_user')
            ->where('kid_id', $kid->id)
            ->where('user_id', $user->id)
            ->first();
            
        // L'utilisateur doit avoir un accès (complet ou lecture seule)
        return $access !== null;
    }

    public function addAnnotation(User $user, Record $record)
    {
        // Récupérer l'accès de l'utilisateur à l'enfant
        $access = DB::table('kid_user')
            ->where('kid_id', $record->kid_id)
            ->where('user_id', $user->id)
            ->first();
        
        // Autoriser uniquement si l'utilisateur a un accès complet
        return $access && $access->access_type === 'full';
    }
    
    public function delete(User $user, Record $record)
    {
        // Même logique que update
        if ($user->isAdmin()) {
            return true;
        }
        
        $access = DB::table('kid_user')
            ->where('kid_id', $record->kid_id)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$access || $access->access_type !== 'full') {
            return false;
        }
        
        return $user->id === $record->user_id;
    }

}

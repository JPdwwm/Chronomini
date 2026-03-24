<?php

namespace App\Http\Controllers\API;

use App\Models\Record;
use App\Models\TimeBreak;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class TimeBreakController extends Controller
{
    public function startBreak(Request $request)
    {
        $request->validate([
            'record_id' => 'required|exists:records,id',
        ]);
    
        // Vérifier si l'enregistrement est toujours actif
        $record = Record::findOrFail($request->record_id);
        if ($record->pick_up_hour) {
            return response()->json(['message' => 'Impossible de mettre en pause un enregistrement terminé'], 400);
        }
    
        // Vérifier s'il y a déjà une pause active
        $activeBreak = TimeBreak::where('record_id', $request->record_id)
                               ->whereNull('break_end')
                               ->first();
        if ($activeBreak) {
            return response()->json(['message' => 'Une pause est déjà en cours'], 400);
        }
    
        // Créer une nouvelle pause
        $timeBreak = TimeBreak::create([
            'record_id' => $request->record_id,
            'break_start' => now()->toTimeString(),
        ]);
    
        return response()->json([
            'message' => 'Pause démarrée avec succès',
            'timeBreak' => $timeBreak
        ], 201);
    }
    
    public function endBreak(Request $request)
    {
        $request->validate([
            'record_id' => 'required|exists:records,id',
        ]);
    
        // Trouver la pause active
        $activeBreak = TimeBreak::where('record_id', $request->record_id)
                               ->whereNull('break_end')
                               ->first();
        
        if (!$activeBreak) {
            return response()->json(['message' => 'Aucune pause en cours pour cet enregistrement'], 404);
        }
    
        // Calculer la durée de la pause
        $startTime = Carbon::parse($activeBreak->break_start);
        $endTime = now();
        $durationInMinutes = $startTime->diffInMinutes($endTime);
    
        // Mettre à jour la pause avec l'heure de fin et la durée totale
        $activeBreak->break_end = $endTime->toTimeString();
        $activeBreak->total = $durationInMinutes;
        $activeBreak->save();
    
        return response()->json([
            'message' => 'Pause terminée avec succès',
            'timeBreak' => $activeBreak,
            'duration' => [
                'minutes' => $durationInMinutes
            ]
        ], 200);
    }

    public function checkActiveBreak($recordId)
    {
        // Rechercher une pause active (où break_end est null) pour cet enregistrement
        $activeBreak = TimeBreak::where('record_id', $recordId)
                             ->whereNull('break_end')
                             ->first();
        
        return response()->json([
            'hasActiveBreak' => $activeBreak !== null,
            'activeBreak' => $activeBreak
        ]);
    }
}

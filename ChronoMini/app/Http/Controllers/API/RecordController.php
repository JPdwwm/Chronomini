<?php

namespace App\Http\Controllers\API;

use App\Models\Kid;
use App\Models\User;
use App\Models\Record;
use App\Models\TimeBreak;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $this->authorize('viewAll', Record::class);

        // On récupère tous les relevés
        $record = Record::all();
                
        // On retourne les informations des utilisateurs en format JSON
        return response()->json($record);
    }

    public function showMyRecords()
    {   
        // Get authenticated user
        $userId = Auth::user()->id;
    
        // On récupère tous les enregistrements de l'utilisateur authentifié
        $records = Record::with('kid') // Charger la relation avec Kid
                         ->where('user_id', $userId)
                         ->paginate(31); // 31 enregistrements par page maximum
    
        // On retourne les enregistrements en format JSON
        return response()->json($records);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function startRecording(Kid $kid)
    {
        // Récupère l'utilisateur authentifié
        $user = Auth::user();
    
        // Vérifier que l'utilisateur est autorisé à enregistrer la dépose pour cet enfant
        $this->authorize('startRecording',[Record::class, $kid]);
    
        // Vérifier s'il y a déjà un enregistrement en cours pour cet enfant
        $activeRecord = Record::where('kid_id', $kid->id)
                            ->whereNull('pick_up_hour')
                            ->first();
    
        if ($activeRecord) {
        // Vérifier si l'enregistrement actif a été créé par l'utilisateur courant
        if ($activeRecord->user_id === $user->id) {
            return response()->json([
                'message' => "Vous avez déjà un enregistrement actif pour cet enfant. Veuillez le compléter avant d'en commencer un nouveau."
            ], 400);
            } else {
            // Obtenir le nom de l'utilisateur qui a créé l'enregistrement actif
            $creatorUser = User::find($activeRecord->user_id);
            $creatorName = $creatorUser ? $creatorUser->first_name . ' ' . $creatorUser->last_name : 'Un autre utilisateur';
                                    
            return response()->json([
                'message' => $creatorName . ' a déjà un enregistrement actif pour cet enfant. Un seul enregistrement actif est autorisé à la fois.'
            ], 400);
        }
    }
    
        // Créer un nouvel enregistrement pour la dépose
        $record = Record::create([
            'user_id' => $user->id,
            'kid_id' => $kid->id,
            'drop_hour' => now()->toTimeString(),
            'date' => now()->format('Y-m-d'),
        ]);
    
        return response()->json([
            'message' => 'Dépose enregistrée avec succès',
            'record' => $record
        ], 201);
    }

    public function stopRecording(Kid $kid)
    {
        $user = Auth::user();
    
        $this->authorize('stopRecording', [Record::class, $kid]);
        
        $activeRecord = Record::where('kid_id', $kid->id)
                              ->whereNull('pick_up_hour')
                              ->first();
    
        if (!$activeRecord) {
            return response()->json(['message' => 'Aucun pointage actif pour cet enfant.'], 404);
        }
    
        // Vérifier s'il y a une pause active et la terminer automatiquement
        $activeBreak = TimeBreak::where('record_id', $activeRecord->id)
                               ->whereNull('break_end')
                               ->first();
        if ($activeBreak) {
            $startTime = Carbon::parse($activeBreak->break_start);
            $endTime = Carbon::now();
            $durationInMinutes = $startTime->diffInMinutes($endTime);
    
            $activeBreak->break_end = $endTime->toTimeString();
            $activeBreak->total = $durationInMinutes;
            $activeBreak->save();
        }
    
        $activeRecord->pick_up_hour = now()->toTimeString();
        $activeRecord->save();
    
        // Calcul de la durée en heures et minutes
        $dropHour = Carbon::parse($activeRecord->drop_hour);
        $pickUpHour = Carbon::now();
        $durationInMinutes = $dropHour->diffInMinutes($pickUpHour);
    
        // Calculer le total des pauses
        $totalBreaksMinutes = TimeBreak::where('record_id', $activeRecord->id)
                                       ->sum('total');
    
        // Déduire les pauses du temps total
        $effectiveDurationInMinutes = $durationInMinutes - $totalBreaksMinutes;
        if ($effectiveDurationInMinutes < 0) $effectiveDurationInMinutes = 0;
    
        // Calcul des heures et minutes
        $hours = floor($effectiveDurationInMinutes / 60);
        $minutes = $effectiveDurationInMinutes % 60;
    
        // Mise à jour en heures décimales
        $activeRecord->amount_hours = $effectiveDurationInMinutes / 60;
        $activeRecord->save();
    
        return response()->json([
            'message' => 'Récupération enregistrée avec succès',
            'record' => $activeRecord,
            'duration' => [
                'hours' => $hours,
                'minutes' => $minutes
            ],
            'totalBreakTime' => $totalBreaksMinutes
        ], 200);
    }
    
    /**
     * Display the specified resource.
     */
    public function showOneRecord(Record $record)
    {   
        $this->authorize('showOneRecord', $record);

        if (!$record) {
            return response()->json(['message' => 'Relevé non trouvé'], 404);
        }

        return response()->json($record);
    }

    public function getKidRecords(Kid $kid)
    {   
        $this->authorize('viewKidRecords', [Record::class, $kid]);
        
        // Récupérer l'utilisateur authentifié
        $user = Auth::user();
        
        // Récupérer l'accès de l'utilisateur à l'enfant
        $access = DB::table('kid_user')
            ->where('kid_id', $kid->id)
            ->where('user_id', $user->id)
            ->first();
        
        $query = Record::where('kid_id', $kid->id);
        
        // Si l'accès est en lecture seule avec une date de fin, filtrer les records par date
        if ($access && $access->access_type === 'readonly' && $access->end_date) {
            $query->whereDate('created_at', '<=', $access->end_date);
        }
        
        $records = $query->orderBy('date', 'desc')
                        ->orderBy('drop_hour', 'desc')
                        ->get();
    
        return response()->json($records);
    }

    /**
 * Ajouter une annotation à un record existant.
 */
    public function addAnnotation(Request $request, Record $record)
    {
        $this->authorize('addAnnotation', $record);
    
        $request->validate([
            'annotation' => 'required|string|max:255',
        ]);
    
        $record->annotation = $request->annotation;
        $record->save();
    
    return response()->json([
        'message' => 'Annotation ajoutée avec succès.',
        'record' => $record
    ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteRecord(Record $record)
    {
        $this->authorize('delete', $record);
    
        $record->delete();
    
        return response()->json(['message' => 'Record deleted successfully']);
    }
}
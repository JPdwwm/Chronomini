<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\KidController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RecordController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\UserLinkController;
use App\Http\Controllers\API\TimeBreakController;
use App\Http\Controllers\PasswordResetController;

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Route pour l'enregistrement
Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:register');
Route::post('/verification', [RegisterController::class, 'verification'])->name('verification')->middleware('throttle:verification');


// Groupe de routes protégées par le middleware 'auth:sanctum'
Route::middleware([EnsureFrontendRequestsAreStateful::class,'auth:sanctum', 'throttle:api'])->group(function ()  {
    Route::apiResource('users', UserController::class);

    Route::get('/user', [ProfileController::class, 'profile']); 
    Route::put('/user', [ProfileController::class, 'update']);
    Route::delete('/user', [ProfileController::class, 'destroy']);

    Route::get('/kids', [KidController::class, 'index']);
    Route::get('/mykids', [KidController::class, 'showMyKids']);
    Route::get('/mykid/{kid}', [KidController::class, 'showOneKid']);
    Route::post('/createkid', [KidController::class, 'createKid']);
    Route::put('/updatekid/{kid}', [KidController::class, 'updateKid']);
    Route::delete('/deletekid/{kid}', [KidController::class, 'deleteKid']);

    Route::get('/records', [RecordController::class, 'index']);
    Route::post('/{kid}/record/start', [RecordController::class, 'startRecording']);
    Route::post('/{kid}/record/stop', [RecordController::class, 'stopRecording']);
    Route::get('/myrecords', [RecordController::class, 'showMyRecords']);
    Route::get('/myrecord/{record}', [RecordController::class, 'showOneRecord']);
    Route::get('/kid/{kid}/records', [RecordController::class, 'getKidRecords']);
    Route::post('records/{record}/annotation', [RecordController::class, 'addAnnotation']);
    
    Route::post('/connection-request', [UserLinkController::class, 'sendConnectionRequest']);
    Route::get('/connection-requests/received', [UserLinkController::class, 'getReceivedRequests']);
    Route::get('/connection-requests/sent', [UserLinkController::class, 'getSentRequests']);
    Route::post('/connection-requests/{id}/accept', [UserLinkController::class, 'acceptRequest']);
    Route::post('/connection-requests/{id}/decline', [UserLinkController::class, 'declineRequest']);
    Route::post('/merge-kids', [UserLinkController::class, 'mergeKids']);
    Route::post('/share-kid', [UserLinkController::class, 'shareKid']);
    Route::get('/connected-users', [UserLinkController::class, 'getConnectedUsers']);
    Route::post('/disconnect-user', [UserLinkController::class, 'disconnectUsers']);

    Route::get('/check-duplicates-with-user/{userId}', [UserLinkController::class, 'checkDuplicatesWithUser']);
    Route::post('/share-kid-with-partner', [UserLinkController::class, 'shareKidWithPartner']);

    Route::post('/timebreaks/start', [TimeBreakController::class, 'startBreak']);
    Route::post('/timebreaks/end', [TimeBreakController::class, 'endBreak']);
    Route::get('/timebreaks/check/{recordId}', [TimeBreakController::class, 'checkActiveBreak']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/update/{id}', [UserController::class, 'update']);
    Route::delete('users/delete/{id}',[UserController::class, 'destroy']);

    Route::get('records', [RecordController::class, 'index']);

    Route::get('kids', [KidController::class, 'index']);
});

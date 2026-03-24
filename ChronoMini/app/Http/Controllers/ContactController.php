<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email',
            'message' => 'required|string',
            'subject' => 'required|string|max:255'
        ]);

        // Adresse email de reception
        $recipientEmail = 'contact@chronomini.fr';

        // Envoyer l'email
        try {
            Mail::to($recipientEmail)->send(new ContactFormMail(
                $request->name,
                $request->email,
                $request->subject,
                $request->message
            ));
            
            return response()->json(['message' => 'Votre message a été envoyé avec succès.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du message de contact: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de l\'envoi du message.'], 500);
        }
    }
}
<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
    ) {}

    public function build()
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL'));
        
        return $this->subject('Réinitialisation de votre mot de passe')
            ->view('email-password-reset', [
                'user' => $this->user,
                'frontend_URL' => $frontendUrl
            ]);
    }
}
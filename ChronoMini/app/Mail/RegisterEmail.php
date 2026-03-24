<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class RegisterEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
    ) {}

    public function build()
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL'));
        
        return $this->subject('Vérifiez votre adresse e-mail')
            ->view('email-register', [
                'user' => $this->user,
                'frontend_URL' => $frontendUrl
            ]);
    }
}
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    // Variables passées directement à la vue
    protected $contactData;

    public function __construct($name, $email, $subject, $messageContent)
    {
        $this->contactData = (object)[
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'messageContent' => $messageContent
        ];
    }

    public function build()
    {
        return $this->subject('Message du formulaire de contact: ' . $this->contactData->subject)
            ->view('email-contact-form', [
                'contact' => $this->contactData
            ]);
    }
}
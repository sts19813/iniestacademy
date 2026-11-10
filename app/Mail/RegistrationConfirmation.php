<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RegistrationConfirmation extends Mailable
{
    public function __construct(public Registration $registration, public bool $preview = false) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: ($this->preview ? '[Prueba] ' : '').'Confirmación de inscripción · Iniesta Academy Mérida');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.registration-confirmation', text: 'emails.registration-confirmation-text');
    }
}

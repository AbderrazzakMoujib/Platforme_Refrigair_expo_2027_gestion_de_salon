<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommercialInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $commercial, public string $activationUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Invitation à activer votre accès Refrigair Expo 2027');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.commercial-invitation');
    }
}

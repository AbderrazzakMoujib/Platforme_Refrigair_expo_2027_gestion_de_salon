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

    public function __construct(
        public User $user,
        public string $role,
        public string $activationUrl,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->role) {
            'commercial' => 'REFRIGAIR EXPO 2027 | Activation de votre espace Commercial',
            'responsable_conferences' => 'REFRIGAIR EXPO 2027 | Activation de votre espace Responsable Conférences',
            'responsable_technique' => 'REFRIGAIR EXPO 2027 | Activation de votre espace Responsable Technique',
            default => 'REFRIGAIR EXPO 2027 | Activez votre espace exposant',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-invitation',
            text: 'emails.account-invitation-text',
        );
    }
}

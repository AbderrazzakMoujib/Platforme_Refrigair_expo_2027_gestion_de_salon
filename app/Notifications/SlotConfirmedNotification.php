<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class SlotConfirmedNotification extends SlotNotification
{
    protected function title(): string
    {
        return 'Créneau confirmé';
    }

    protected function message(): string
    {
        return "Votre créneau {$this->slot->code} ({$this->slot->full_label}) est confirmé.";
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = $this->baseMail()
            ->line('Nous avons le plaisir de vous confirmer votre créneau de présentation.')
            ->line('Merci d\'envoyer votre support (PPT, PPTX ou PDF, 20 Mo maximum) au plus tard 7 jours avant le créneau.');

        return $this->nextStep($mail, 'Accéder à mon espace')
            ->salutation('L\'équipe Refrigair Expo 2027');
    }
}

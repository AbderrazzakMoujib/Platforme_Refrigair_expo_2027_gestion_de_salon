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
            ->line(
                'Nous avons le plaisir de vous confirmer votre créneau de présentation.'
            )
            ->line(
                'Merci de transmettre votre support (PPT ou PDF, format 16:9, 20 Mo maximum) au plus tard le 26 janvier 2027 : 7 jours avant le salon.'
            )
            ->line(
                'Veuillez vous présenter 15 minutes avant votre créneau pour le test technique.'
            );

        return $this->nextStep($mail, 'Accéder à mon espace')
            ->salutation("L'équipe Refrigair Expo 2027");
    }
}

<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class SlotSupportReminderNotification extends SlotNotification
{
    protected function title(): string
    {
        return 'Rappel : support de présentation';
    }

    protected function message(): string
    {
        return "Votre créneau {$this->slot->code} aura lieu le {$this->slot->date->format('d/m/Y')}. Merci d'envoyer votre support de présentation au plus tard le 26 janvier 2027.";
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = $this->baseMail()
            ->line(
                "Le salon commence le 2 février 2027 et nous n'avons pas encore reçu votre support."
            )
            ->line(
                'Merci de transmettre votre présentation PPT ou PDF au format 16:9 (20 Mo maximum) au plus tard le 26 janvier 2027.'
            );

        return $this->nextStep($mail, 'Envoyer mon support')
            ->salutation("L'équipe Refrigair Expo 2027");
    }
}

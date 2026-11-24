<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/** Rappel J‑7 : envoi du support de présentation. */
class SlotSupportReminderNotification extends SlotNotification
{
    protected function title(): string
    {
        return 'Rappel : support de présentation';
    }

    protected function message(): string
    {
        return "Votre créneau {$this->slot->code} a lieu le {$this->slot->date->format('d/m/Y')}. Merci d'envoyer votre support.";
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = $this->baseMail()
            ->line('Votre présentation a lieu dans une semaine et nous n\'avons pas encore reçu votre support.')
            ->line('Formats acceptés : PPT, PPTX ou PDF, 20 Mo maximum.');

        return $this->nextStep($mail, 'Envoyer mon support')
            ->salutation('L\'équipe Refrigair Expo 2027');
    }
}

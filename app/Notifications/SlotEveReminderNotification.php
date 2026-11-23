<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/** Rappel la veille : arrivée 15 minutes avant pour le test technique. */
class SlotEveReminderNotification extends SlotNotification
{
    protected function title(): string
    {
        return 'Votre présentation a lieu demain';
    }

    protected function message(): string
    {
        return "Créneau {$this->slot->code} demain à {$this->slot->start_label} : présentez-vous 15 min avant pour le test technique.";
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = $this->baseMail()
            ->line('Votre présentation a lieu demain.')
            ->line('Merci de vous présenter **15 minutes avant** le début de votre créneau ('.$this->slot->start_label.') pour le test technique.');

        return $this->nextStep($mail, 'Voir mon créneau')
            ->salutation('L\'équipe Refrigair Expo 2027');
    }
}

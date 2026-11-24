<?php

namespace App\Notifications;

use App\Models\Slot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base commune des notifications liées à un créneau.
 *
 * Exposant avec compte : e‑mail + notification dans l'espace exposant.
 * Exposant sans compte (adresse de la fiche) : e‑mail seul.
 */
abstract class SlotNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Slot $slot) {}

    public function via(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : ['mail', 'database'];
    }

    abstract protected function title(): string;

    abstract protected function message(): string;

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'conference',
            'title' => $this->title(),
            'message' => $this->message(),
            'slot_id' => $this->slot->id,
        ];
    }

    /** En-tête commun des e‑mails : récapitulatif du créneau. */
    protected function baseMail(): MailMessage
    {
        $slot = $this->slot->loadMissing('space');

        $mail = (new MailMessage)
            ->subject($this->title().' – '.$slot->code.' – Refrigair Expo 2027')
            ->greeting('Bonjour,')
            ->line('**Espace :** '.$slot->space->type->label())
            ->line('**Créneau :** '.$slot->full_label)
            ->line('**Lieu :** OFEC Casablanca');

        if ($slot->title) {
            $mail->line('**Sujet :** '.$slot->title);
        }

        return $mail;
    }

    protected function spaceUrl(): string
    {
        return route('exhibitor.slots.index');
    }

    /**
     * Lien vers l'espace exposant, ou — pour un titulaire non exposant qui
     * n'a pas de compte — invitation à passer par son commercial.
     */
    protected function nextStep(MailMessage $mail, string $actionLabel): MailMessage
    {
        if ($this->slot->exhibitor_id !== null) {
            return $mail->action($actionLabel, $this->spaceUrl());
        }

        return $mail->line('Pour transmettre votre support ou pour toute question, contactez votre interlocuteur commercial Refrigair Expo, qui s\'en occupera pour vous.');
    }
}

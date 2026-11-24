<?php

namespace App\Notifications;

use App\Models\SlotRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlotRequestRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SlotRequest $slotRequest) {}

    public function via(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Demande de créneau non retenue – Refrigair Expo 2027')
            ->greeting('Bonjour,')
            ->line('Votre demande de créneau « '.$this->slotRequest->title.' » n\'a pas pu être retenue.')
            ->line('**Motif :** '.$this->slotRequest->rejection_reason)
            ->line('Vous pouvez envoyer une nouvelle demande sur d\'autres créneaux disponibles.')
            ->action('Voir les créneaux disponibles', route('exhibitor.slots.index'))
            ->salutation('L\'équipe Refrigair Expo 2027');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'conference',
            'title' => 'Demande de créneau refusée',
            'message' => 'Votre demande « '.$this->slotRequest->title.' » a été refusée : '.$this->slotRequest->rejection_reason,
            'slot_request_id' => $this->slotRequest->id,
        ];
    }
}

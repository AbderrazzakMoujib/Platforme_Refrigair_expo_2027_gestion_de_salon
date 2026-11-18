<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SupportTicketNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SupportTicket $ticket,
        public string $event
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $company = $this->ticket->exhibitor?->company_name
            ?? 'Un exposant';

        [$title, $message] = match ($this->event) {
            'created' => [
                'Nouvelle demande d’assistance',
                "{$company} a envoyé une demande : {$this->ticket->subject}",
            ],
            'exhibitor_reply' => [
                'Nouveau message exposant',
                "{$company} a répondu à la demande #{$this->ticket->id}.",
            ],
            'commercial_reply' => [
                'Réponse de votre commercial',
                "Votre commercial a répondu à la demande #{$this->ticket->id}.",
            ],
            'status_updated' => [
                'Statut de demande modifié',
                "Votre demande #{$this->ticket->id} est maintenant : {$this->ticket->status_label}.",
            ],
            default => [
                'Mise à jour assistance',
                "La demande #{$this->ticket->id} a été mise à jour.",
            ],
        };

        return [
            'type' => 'support',
            'event' => $this->event,
            'title' => $title,
            'message' => $message,
            'ticket_id' => $this->ticket->id,
        ];
    }
}

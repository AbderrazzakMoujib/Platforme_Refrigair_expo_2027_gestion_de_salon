<?php

namespace App\Notifications;

use App\Models\Exhibitor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewExhibitorNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Exhibitor $exhibitor
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'exhibitor',
            'title' => 'Nouvel exposant enregistré',
            'message' => $this->exhibitor->company_name
                . ' a été ajouté à la plateforme.',
            'url' => route(
                'admin.exposants.show',
                $this->exhibitor
            ),
            'exhibitor_id' => $this->exhibitor->id,
        ];
    }
}

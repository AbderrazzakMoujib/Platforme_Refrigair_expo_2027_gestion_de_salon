<?php

namespace App\Notifications;

use App\Models\EquipmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewEquipmentRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        public EquipmentRequest $equipmentRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'equipment_request',
            'title' => 'Nouvelle demande d’équipements',
            'message' => 'Une demande a été enregistrée par '
                . ($this->equipmentRequest->exhibitor?->company_name ?? 'un exposant')
                . '.',
            'url' => route(
                'admin.demandes.show',
                $this->equipmentRequest
            ),
            'equipment_request_id' => $this->equipmentRequest->id,
        ];
    }
}

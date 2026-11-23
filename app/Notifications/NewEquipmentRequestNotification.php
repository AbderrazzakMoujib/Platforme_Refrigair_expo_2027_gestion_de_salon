<?php

namespace App\Notifications;

use App\Enums\UserRole;
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
        $isCommercial = $notifiable->role
            === UserRole::Commercial;

        $route = $isCommercial
            ? 'commercial.equipment-requests.show'
            : 'admin.demandes.show';

        return [
            'type' => 'equipment_request',

            'title' => 'Nouvelle demande d’équipements',

            'message' => 'Nouvelle demande envoyée par '
                . (
                    $this->equipmentRequest
                        ->exhibitor?->company_name
                    ?? 'un exposant'
                )
                . '.',

            'equipment_request_id' => $this->equipmentRequest->id,

            'url' => route(
                $route,
                $this->equipmentRequest
            ),
        ];
    }
}

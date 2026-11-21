<?php

namespace App\Notifications;

use App\Enums\UserRole;
use App\Models\EquipmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EquipmentRequestStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public EquipmentRequest $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $approved = $this->order->status === 'approved';

        $isExhibitor = $notifiable->role === UserRole::Exposant;

        return [
            'type' => 'equipment_request',
            'title' => $approved
                ? 'Demande d’équipements approuvée'
                : 'Demande d’équipements refusée',
            'message' => 'La demande #'
                . $this->order->id
                . ' a été '
                . ($approved ? 'approuvée.' : 'refusée.'),
            'equipment_request_id' => $this->order->id,
            'url' => $isExhibitor
                ? route('exhibitor.requests.index')
                : route('admin.demandes.show', $this->order),
        ];
    }
}

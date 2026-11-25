<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConferenceManagerNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $eventType,
        public string $title,
        public string $message,
        public ?int $slotRequestId = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->eventType,
            'title' => $this->title,
            'message' => $this->message,
            'slot_request_id' => $this->slotRequestId,
        ];
    }
}

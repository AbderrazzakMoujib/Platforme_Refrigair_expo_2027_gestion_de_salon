<?php

namespace App\Services;

use App\Models\SlotRequest;
use App\Models\User;
use App\Notifications\ConferenceManagerNotification;
use Illuminate\Support\Facades\Notification;

class ConferenceManagerNotificationService
{
    public function newRequest(SlotRequest $request): void
    {
        $request->loadMissing('exhibitor');

        $company = $request->exhibitor?->company_name
            ?? 'Un exposant';

        $this->send(
            'conference_request',
            'Nouvelle demande de réservation',
            "{$company} a soumis une demande : {$request->title}",
            $request->id
        );
    }

    public function accepted(SlotRequest $request): void
    {
        $request->loadMissing('exhibitor', 'assignedSlot');

        $company = $request->exhibitor?->company_name
            ?? 'Un exposant';

        $code = $request->assignedSlot?->code ?? '—';

        $this->send(
            'conference_accepted',
            'Demande validée',
            "La demande de {$company} a été validée pour {$code}.",
            $request->id
        );
    }

    public function rejected(SlotRequest $request): void
    {
        $request->loadMissing('exhibitor');

        $company = $request->exhibitor?->company_name
            ?? 'Un exposant';

        $this->send(
            'conference_rejected',
            'Demande refusée',
            "La demande de {$company} a été refusée.",
            $request->id
        );
    }

    private function send(
        string $type,
        string $title,
        string $message,
        ?int $requestId = null
    ): void {
        $responsables = User::query()
            ->where('role', 'responsable_conferences')
            ->where('is_active', true)
            ->get();

        if ($responsables->isEmpty()) {
            return;
        }

        Notification::send(
            $responsables,
            new ConferenceManagerNotification(
                $type,
                $title,
                $message,
                $requestId
            )
        );
    }
}

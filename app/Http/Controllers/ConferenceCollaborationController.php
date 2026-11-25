<?php

namespace App\Http\Controllers;

use App\Enums\SlotRequestStatus;
use App\Enums\UserRole;
use App\Models\SlotRequest;
use App\Models\User;
use App\Notifications\ConferenceCollaborationNotification;
use App\Services\SlotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConferenceCollaborationController extends Controller
{
    private function authorizeAccess(
        Request $request,
        SlotRequest $slotRequest
    ): bool {
        $user = $request->user();

        abort_unless($user && $user->is_active, 403);

        $isManager = $user->role === UserRole::ResponsableConferences;

        $isOwner = $user->role === UserRole::Exposant
            && $user->exhibitorProfile()
                ->whereKey($slotRequest->exhibitor_id)
                ->exists();

        abort_unless($isManager || $isOwner, 403);

        abort_unless(
            $slotRequest->status === SlotRequestStatus::Accepted
                && $slotRequest->assigned_slot_id !== null,
            403,
            'La réservation doit être confirmée.'
        );

        $slotRequest->loadMissing('assignedSlot');

        abort_unless(
            $slotRequest->assignedSlot
                && (int) $slotRequest->assignedSlot->exhibitor_id
                    === (int) $slotRequest->exhibitor_id
                && $slotRequest->assignedSlot->status->isSold(),
            403
        );

        return $isManager;
    }

    private function notifyManagers(
        SlotRequest $slotRequest,
        string $type,
        string $title,
        string $message
    ): void {
        $managers = User::query()
            ->where('role', UserRole::ResponsableConferences->value)
            ->where('is_active', true)
            ->get();

        Notification::send(
            $managers,
            new ConferenceCollaborationNotification(
                $type,
                $title,
                $message,
                $slotRequest->id
            )
        );
    }

    private function notifyExhibitor(
        SlotRequest $slotRequest,
        string $type,
        string $title,
        string $message
    ): void {
        $slotRequest->loadMissing('exhibitor.user');

        $user = $slotRequest->exhibitor?->user;

        if (!$user || !$user->is_active) {
            return;
        }

        $user->notify(
            new ConferenceCollaborationNotification(
                $type,
                $title,
                $message,
                $slotRequest->id
            )
        );
    }

    public function show(
        Request $request,
        SlotRequest $slotRequest
    ): View {
        $isManager = $this->authorizeAccess(
            $request,
            $slotRequest
        );

        $slotRequest->load([
            'exhibitor',
            'assignedSlot.space',
            'supportReviewer',
        ]);

        $slotRequest->conferenceMessages()
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $slotRequest->conferenceMessages()
            ->with('sender:id,name,role,profile_photo')
            ->orderBy('id')
            ->get();

        $data = [
            'reservation' => $slotRequest,
            'messages' => $messages,
        ];

        if ($isManager) {
            return view(
                'responsable-conferences.collaboration',
                $data
            );
        }

        return view('exhibitor.slots.collaboration', [
            ...$data,
            'user' => $request->user(),
            'exhibitor' => $slotRequest->exhibitor,
        ]);
    }

    public function uploadSupport(
        Request $request,
        SlotRequest $slotRequest,
        SlotService $service
    ): RedirectResponse {
        $isManager = $this->authorizeAccess(
            $request,
            $slotRequest
        );

        abort_if($isManager, 403);

        $data = $request->validate([
            'support' => [
                'required',
                'file',
                'mimes:ppt,pptx,pdf',
                'extensions:ppt,pptx,pdf',
                'max:20480',
            ],
            'support_details' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
        ]);

        $slotRequest = $service->uploadConferenceSupport(
            $slotRequest,
            $request->file('support'),
            $data['support_details'],
            $request->user()
        );

        $slotRequest->loadMissing('exhibitor');

        $this->notifyManagers(
            $slotRequest,
            'conference_support',
            'Nouveau support de présentation',
            "{$slotRequest->exhibitor->company_name} a envoyé un support à vérifier."
        );

        return back()->with(
            'status',
            'Votre support a été envoyé au Responsable Conférences.'
        );
    }

    public function reviewSupport(
        Request $request,
        SlotRequest $slotRequest,
        SlotService $service
    ): RedirectResponse {
        $isManager = $this->authorizeAccess($request, $slotRequest);
        abort_unless($isManager, 403);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'revision_requested'])],
            'note' => ['nullable', 'required_if:decision,revision_requested', 'string', 'max:2000'],
        ]);

        $slotRequest = $service->reviewConferenceSupport(
            $slotRequest,
            $data['decision'],
            $data['note'] ?? null,
            $request->user()
        );

    $approved = $data['decision'] === 'approved';

    $this->notifyExhibitor(
        $slotRequest,
        'conference_support_review',
        $approved
            ? 'Support approuvé'
            : 'Modification du support demandée',
        $approved
            ? 'Votre support de présentation a été approuvé.'
            : 'Le Responsable Conférences a demandé des modifications à votre support.'
    );

    return back()->with(
        'status',
        $approved
            ? 'Le support a été approuvé.'
            : 'La demande de modification a été envoyée.'
    );
}

    public function sendMessage(
        Request $request,
        SlotRequest $slotRequest
    ): RedirectResponse {
        $isManager = $this->authorizeAccess(
            $request,
            $slotRequest
        );

        $data = $request->validate([
            'body' => [
                'required',
                'string',
                'max:4000',
            ],
        ]);

        $body = trim($data['body']);

        if ($body === '') {
            return back()->withErrors([
                'body' => 'Veuillez saisir un message.',
            ]);
        }

        $slotRequest->conferenceMessages()->create([
            'sender_id' => $request->user()->id,
            'body' => $body,
        ]);

        $slotRequest->loadMissing('exhibitor');

        if ($isManager) {
            $this->notifyExhibitor(
                $slotRequest,
                'conference_message',
                'Nouveau message du Responsable Conférences',
                'Vous avez reçu un message concernant votre présentation.'
            );
        } else {
            $this->notifyManagers(
                $slotRequest,
                'conference_message',
                'Nouveau message de l’exposant',
                "{$slotRequest->exhibitor->company_name} a envoyé un message."
            );
        }

        return back()->with(
            'status',
            'Message envoyé.'
        );
    }

    public function downloadSupport(
        Request $request,
        SlotRequest $slotRequest
    ): StreamedResponse {
        $this->authorizeAccess($request, $slotRequest);

        $slot = $slotRequest->assignedSlot;

        abort_unless(
            $slot->support_path
                && Storage::disk('local')->exists($slot->support_path),
            404
        );

        return Storage::disk('local')->download(
            $slot->support_path,
            $slot->support_original_name
                ?? basename($slot->support_path)
        );
    }
}

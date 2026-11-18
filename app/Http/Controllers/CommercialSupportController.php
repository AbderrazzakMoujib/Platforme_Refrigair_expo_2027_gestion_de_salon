<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Notifications\SupportTicketNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommercialSupportController extends Controller
{
    private function owned(
        Request $request,
        SupportTicket $ticket
    ): void {
        abort_unless(
            (int) $ticket->commercial_id ===
                (int) $request->user()->id,
            404
        );

        // Le commercial doit toujours être
        // responsable de l'exposant actuellement.
        abort_unless(
            $ticket->exhibitor()
                ->where('commercial_id', $request->user()->id)
                ->exists(),
            404
        );
    }

    private function layoutData(Request $request): array
    {
        $commercial = $request->user();

        return [
            'commercial' => $commercial,
            'commercialInitials' => $commercial->initials,
        ];
    }

    public function index(Request $request): View
    {
        $commercial = $request->user();

        $tickets = SupportTicket::query()
            ->where('commercial_id', $commercial->id)
            ->whereHas('exhibitor', function ($query) use ($commercial) {
                $query->where(
                    'commercial_id',
                    $commercial->id
                );
            })
            ->with('exhibitor:id,company_name')
            ->latest()
            ->paginate(10);

        return view('commercial.support.index', [
            ...$this->layoutData($request),
            'tickets' => $tickets,
        ]);
    }

    public function show(
        Request $request,
        SupportTicket $ticket
    ): View {
        $this->owned($request, $ticket);

        $ticket->load([
            'exhibitor',
            'messages.user',
        ]);

        return view('commercial.support.show', [
            ...$this->layoutData($request),
            'ticket' => $ticket,
        ]);
    }

    public function reply(
        Request $request,
        SupportTicket $ticket
    ): RedirectResponse {
        $this->owned($request, $ticket);

        $validated = $request->validate([
            'message' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        DB::transaction(function () use (
            $request,
            $ticket,
            $validated
        ) {
            $lockedTicket = SupportTicket::query()
                ->lockForUpdate()
                ->findOrFail($ticket->id);

            abort_if(
                $lockedTicket->status === 'resolved',
                409,
                'Cette demande est déjà résolue.'
            );

            $lockedTicket->messages()->create([
                'user_id' => $request->user()->id,
                'message' => $validated['message'],
            ]);

            if ($lockedTicket->status === 'pending') {
                $lockedTicket->update([
                    'status' => 'in_progress',
                ]);
            }
        });

        $ticket->refresh()->load('exhibitor.user');

        // Notification à l'exposant.
        $ticket->exhibitor?->user?->notify(
            new SupportTicketNotification(
                $ticket,
                'commercial_reply'
            )
        );

        return back()->with(
            'status',
            'Votre réponse a été envoyée.'
        );
    }

    public function updateStatus(
        Request $request,
        SupportTicket $ticket
    ): RedirectResponse {
        $this->owned($request, $ticket);

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'pending',
                    'in_progress',
                    'resolved',
                ]),
            ],
        ]);

        $changed = DB::transaction(function () use (
            $ticket,
            $validated
        ) {
            $lockedTicket = SupportTicket::query()
                ->lockForUpdate()
                ->findOrFail($ticket->id);

            if (
                $lockedTicket->status ===
                $validated['status']
            ) {
                return false;
            }

            $lockedTicket->update([
                'status' => $validated['status'],
                'resolved_at' =>
                    $validated['status'] === 'resolved'
                        ? now()
                        : null,
            ]);

            return true;
        });

        if ($changed) {
            $ticket->refresh()->load('exhibitor.user');

            // Notification à l'exposant uniquement
            // si le statut a réellement changé.
            $ticket->exhibitor?->user?->notify(
                new SupportTicketNotification(
                    $ticket,
                    'status_updated'
                )
            );
        }

        return back()->with(
            'status',
            $changed
                ? 'Statut mis à jour.'
                : 'Le statut est déjà à jour.'
        );
    }
}

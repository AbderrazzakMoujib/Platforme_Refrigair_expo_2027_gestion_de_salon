<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Notifications\SupportTicketNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExhibitorSupportController extends Controller
{
    private function profile(Request $request)
    {
        return $request->user()
            ->exhibitorProfile()
            ->firstOrFail();
    }

    private function ownedTicket(
        Request $request,
        SupportTicket $ticket
    ): void {
        $exhibitor = $this->profile($request);

        abort_unless(
            (int) $ticket->exhibitor_id === (int) $exhibitor->id,
            404
        );
    }

    public function index(Request $request): View
    {
        $exhibitor = $this->profile($request);

        $tickets = SupportTicket::query()
            ->where('exhibitor_id', $exhibitor->id)
            ->withCount('messages')
            ->latest()
            ->paginate(10);

        return view('exhibitor.support.index', [
            'user' => $request->user(),
            'exhibitor' => $exhibitor->load('commercial'),
            'tickets' => $tickets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $exhibitor = $this->profile($request);

        $validated = $request->validate([
            'subject' => [
                'required',
                'string',
                'max:255',
            ],
            'category' => [
                'required',
                Rule::in([
                    'badges',
                    'stand',
                    'equipment',
                    'account',
                    'other',
                ]),
            ],
            'message' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        if (
            !$exhibitor->commercial_id ||
            !$exhibitor->commercial()
                ->where('is_active', true)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'commercial' =>
                    'Aucun commercial actif n’est attribué à votre entreprise.',
            ]);
        }

        $ticket = DB::transaction(function () use (
            $exhibitor,
            $request,
            $validated
        ) {
            $ticket = SupportTicket::create([
                'exhibitor_id' => $exhibitor->id,
                'commercial_id' => $exhibitor->commercial_id,
                'subject' => $validated['subject'],
                'category' => $validated['category'],
                'status' => 'pending',
            ]);

            $ticket->messages()->create([
                'user_id' => $request->user()->id,
                'message' => $validated['message'],
            ]);

            return $ticket;
        });

        // Notification au commercial responsable.
        $ticket->load(['exhibitor', 'commercial']);

        $ticket->commercial?->notify(
            new SupportTicketNotification(
                $ticket,
                'created'
            )
        );

        return redirect()
            ->route('exhibitor.support.show', $ticket)
            ->with(
                'status',
                'Votre demande a été envoyée avec succès.'
            );
    }

    public function show(
        Request $request,
        SupportTicket $ticket
    ): View {
        $this->ownedTicket($request, $ticket);

        $exhibitor = $this->profile($request);

        $ticket->load([
            'commercial',
            'messages.user',
        ]);

        return view('exhibitor.support.show', [
            'user' => $request->user(),
            'exhibitor' => $exhibitor,
            'ticket' => $ticket,
        ]);
    }

    public function reply(
        Request $request,
        SupportTicket $ticket
    ): RedirectResponse {
        $this->ownedTicket($request, $ticket);

        $validated = $request->validate([
            'message' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        // Enregistrement du message seulement
        // si le ticket n'est pas résolu.
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
        });

        // Notification au commercial.
        $ticket->load(['exhibitor', 'commercial']);

        $ticket->commercial?->notify(
            new SupportTicketNotification(
                $ticket,
                'exhibitor_reply'
            )
        );

        return back()->with(
            'status',
            'Votre message a été envoyé.'
        );
    }
}

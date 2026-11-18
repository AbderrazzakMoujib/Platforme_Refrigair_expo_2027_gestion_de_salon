<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountNotificationController extends Controller
{
    private function allowed(Request $request): void
    {
        abort_unless(
            in_array(
                $request->user()->role,
                [
                    UserRole::Exposant,
                    UserRole::Commercial,
                ],
                true
            ),
            403
        );
    }

    private function routePrefix(Request $request): string
    {
        return $request->user()->role === UserRole::Exposant
            ? 'exhibitor.notifications'
            : 'commercial.notifications';
    }

    public function index(Request $request): View
    {
        $this->allowed($request);

        $user = $request->user();

        $isExhibitor = $user->role === UserRole::Exposant;

        $notifications = $user
            ->notifications()
            ->latest()
            ->paginate(12);

        $unreadCount = $user
            ->unreadNotifications()
            ->count();

        return view('account.notifications.index', [
            'user' => $user,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'isExhibitor' => $isExhibitor,

            'exhibitor' => $isExhibitor
                ? $user->exhibitorProfile()->firstOrFail()
                : null,
        ]);
    }

    public function open(
        Request $request,
        string $notification
    ): RedirectResponse {
        $this->allowed($request);

        $user = $request->user();

        // Important : notification appartenant
        // uniquement à l'utilisateur connecté.
        $item = $user
            ->notifications()
            ->findOrFail($notification);

        $item->markAsRead();

        $data = $item->data ?? [];

        $fallback = route(
            $this->routePrefix($request) . '.index'
        );

        if (
            ($data['type'] ?? null) !== 'support'
            || !isset($data['ticket_id'])
            || !ctype_digit((string) $data['ticket_id'])
        ) {
            return redirect()->to($fallback);
        }

        $ticketId = (int) $data['ticket_id'];

        if ($user->role === UserRole::Exposant) {
            $exhibitor = $user
                ->exhibitorProfile()
                ->firstOrFail();

            $exists = SupportTicket::query()
                ->where(
                    'exhibitor_id',
                    $exhibitor->id
                )
                ->whereKey($ticketId)
                ->exists();

            if (!$exists) {
                return redirect()->to($fallback);
            }

            return redirect()->route(
                'exhibitor.support.show',
                $ticketId
            );
        }

        // Accès Commercial :
        // ticket attribué au commercial connecté
        // ET exposant toujours sous sa responsabilité.
        $exists = SupportTicket::query()
            ->where('commercial_id', $user->id)
            ->whereHas(
                'exhibitor',
                fn ($query) => $query->where(
                    'commercial_id',
                    $user->id
                )
            )
            ->whereKey($ticketId)
            ->exists();

        if (!$exists) {
            return redirect()->to($fallback);
        }

        return redirect()->route(
            'commercial.support.show',
            $ticketId
        );
    }

    public function markAllRead(
        Request $request
    ): RedirectResponse {
        $this->allowed($request);

        $request->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return back()->with(
            'status',
            'Toutes les notifications ont été marquées comme lues.'
        );
    }
}

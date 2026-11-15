<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'filter' => ['nullable', 'in:all,unread,read'],
        ]);

        $admin = $request->user();
        $filter = $filters['filter'] ?? 'all';

        $query = $admin->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        if ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return view('admin.notifications.index', [
            'admin' => $admin,
            'adminInitials' => collect(
                preg_split('/\s+/', trim($admin->name))
            )
                ->filter()
                ->take(2)
                ->map(fn (string $part) =>
                    mb_strtoupper(mb_substr($part, 0, 1))
                )
                ->implode('') ?: 'A',

            'notifications' => $query
                ->latest()
                ->paginate(12)
                ->withQueryString(),

            'filter' => $filter,
            'totalCount' => $admin->notifications()->count(),
            'unreadCount' => $admin->unreadNotifications()->count(),
            'readCount' => $admin->readNotifications()->count(),
        ]);
    }

    public function open(
        Request $request,
        string $notification
    ): RedirectResponse {
        $item = $request->user()
            ->notifications()
            ->findOrFail($notification);

        $item->markAsRead();

        $data = $item->data;

        // URLs générées côté serveur, jamais confiance
        // à une URL arbitraire stockée en base.
        $destination = match ($data['type'] ?? null) {
            'equipment_request' => (
                isset($data['equipment_request_id'])
                ? route('admin.demandes.show', $data['equipment_request_id'])
                : route('admin.demandes.index')
            ),
            'exhibitor' => (
                isset($data['exhibitor_id'])
                ? route('admin.exposants.show', $data['exhibitor_id'])
                : route('admin.exposants.index')
            ),
            default => route('admin.notifications.index'),
        };

        return redirect()->to($destination);
    }

    public function markRead(
        Request $request,
        string $notification
    ): RedirectResponse {
        $item = $request->user()
            ->notifications()
            ->findOrFail($notification);

        $item->markAsRead();

        return back()->with(
            'status',
            'Notification marquée comme lue.'
        );
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return back()->with(
            'status',
            'Toutes les notifications ont été marquées comme lues.'
        );
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'filter' => [
                'nullable',
                Rule::in(['all', 'unread', 'read']),
            ],
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'type' => [
                'nullable',
                Rule::in([
                    'equipment_request',
                    'exhibitor',
                ]),
            ],
            'period' => [
                'nullable',
                Rule::in(['all', 'today', '7d', '30d']),
            ],
            'selected' => [
                'nullable',
                'uuid',
            ],
        ]);

        $admin = $request->user();

        $filter = $filters['filter'] ?? 'all';
        $period = $filters['period'] ?? 'all';

        $query = $admin->notifications();

        // Statut lu / non lu.
        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        if ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        // Recherche sur titre, message ou données.
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(
                'data',
                'like',
                "%{$search}%"
            );
        }

        // Type de notification.
        if (!empty($filters['type'])) {
            $query->where(
                'data->type',
                $filters['type']
            );
        }

        // Période.
        switch ($period) {
            case 'today':
                $query->whereDate(
                    'created_at',
                    today()
                );
                break;

            case '7d':
                $query->where(
                    'created_at',
                    '>=',
                    now()->subDays(7)
                );
                break;

            case '30d':
                $query->where(
                    'created_at',
                    '>=',
                    now()->subDays(30)
                );
                break;
        }

        $notifications = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Une notification ne peut être consultée
        // que si elle appartient à l'admin connecté.
        $selectedNotification = null;

        if (!empty($filters['selected'])) {
            $selectedNotification = (clone $query)
            ->whereKey($filters['selected'])
            ->first();
        }

        // Afficher la première notification par défaut.
        if (!$selectedNotification) {
            $selectedNotification = $notifications
                ->items()[0] ?? null;
        }

        return view('admin.notifications.index', [
            'admin' => $admin,
            'adminInitials' => $this->initials(
                $admin->name
            ),

            'notifications' => $notifications,
            'selectedNotification' => $selectedNotification,

            'filter' => $filter,
            'period' => $period,

            'totalCount' => $admin
                ->notifications()
                ->count(),

            'unreadCount' => $admin
                ->unreadNotifications()
                ->count(),

            'readCount' => $admin
                ->readNotifications()
                ->count(),

            'todayCount' => $admin
                ->notifications()
                ->whereDate('created_at', today())
                ->count(),
        ]);
    }

    
public function open(
    Request $request,
    string $notification
): RedirectResponse {
    $admin = $request->user();

    // Seules les notifications de cet admin sont accessibles.
    $item = $admin->notifications()
        ->findOrFail($notification);

    $item->markAsRead();

    $data = $item->data ?? [];

    $fallback = route('admin.notifications.index');

    // Notification d'une demande d'équipements
    if (($data['type'] ?? null) === 'equipment_request') {
        $id = $data['equipment_request_id'] ?? null;

        if (
            !is_scalar($id) ||
            !ctype_digit((string) $id) ||
            (int) $id < 1
        ) {
            return redirect()->route('admin.demandes.index');
        }

        $exists = \App\Models\EquipmentRequest::query()
            ->whereKey((int) $id)
            ->exists();

        if (!$exists) {
            return redirect()
                ->route('admin.demandes.index')
                ->with('warning', 'Cette demande n’est plus disponible.');
        }

        return redirect()->route(
            'admin.demandes.show',
            (int) $id
        );
    }

    // Notification d'un nouvel exposant
    if (($data['type'] ?? null) === 'exhibitor') {
        $id = $data['exhibitor_id'] ?? null;

        if (
            !is_scalar($id) ||
            !ctype_digit((string) $id) ||
            (int) $id < 1
        ) {
            return redirect()->route('admin.exposants.index');
        }

        $exists = \App\Models\Exhibitor::query()
            ->whereKey((int) $id)
            ->exists();

        if (!$exists) {
            return redirect()
                ->route('admin.exposants.index')
                ->with('warning', 'Cet exposant n’est plus disponible.');
        }

        return redirect()->route(
            'admin.exposants.show',
            (int) $id
        );
    }

    return redirect()->to($fallback);
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

    public function markAllRead(
        Request $request
    ): RedirectResponse {
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

    private function initials(string $name): string
    {
        return collect(
            preg_split('/\s+/', trim($name))
        )
            ->filter()
            ->take(2)
            ->map(
                fn (string $part) =>
                    mb_strtoupper(
                        mb_substr($part, 0, 1)
                    )
            )
            ->implode('') ?: 'A';
    }
}

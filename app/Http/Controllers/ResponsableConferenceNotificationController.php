<?php

namespace App\Http\Controllers;

use App\Enums\SlotRequestStatus;
use App\Models\SlotRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResponsableConferenceNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => [
                'nullable',
                Rule::in(['all', 'unread', 'read']),
            ],
            'type' => [
                'nullable',
                Rule::in([
    'all',
    'conference_request',
    'conference_accepted',
    'conference_rejected',
    'conference_message',
    'conference_support',
    'conference_support_review',
]),
            ],
            'period' => [
                'nullable',
                Rule::in(['all', '7d', '30d']),
            ],
        ]);

        $user = $request->user();

        $base = $user->notifications();

        $total = (clone $base)->count();

        $unread = $user->unreadNotifications()->count();

        $validations = (clone $base)
            ->where('data->type', 'conference_accepted')
            ->count();

        $refusals = (clone $base)
            ->where('data->type', 'conference_rejected')
            ->count();

        $query = $user->notifications();

        $status = $filters['status'] ?? 'all';

        if ($status === 'unread') {
            $query->whereNull('read_at');
        }

        if ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        $type = $filters['type'] ?? 'all';

        if ($type !== 'all') {
            $query->where('data->type', $type);
        }

        $search = trim($filters['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('data->title', 'like', "%{$search}%")
                    ->orWhere('data->message', 'like', "%{$search}%");
            });
        }

        $period = $filters['period'] ?? 'all';

        if ($period === '7d') {
            $query->where('created_at', '>=', now()->subDays(7));
        }

        if ($period === '30d') {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        $notifications = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('responsable-conferences.notifications', [
            'notifications' => $notifications,
            'filters' => $filters,
            'total' => $total,
            'unread' => $unread,
            'validations' => $validations,
            'refusals' => $refusals,
        ]);
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

    public function open(
        Request $request,
        string $notification
    ): RedirectResponse {
        // Recherche strictement dans les notifications
        // du responsable connecté.
        $item = $request->user()
            ->notifications()
            ->findOrFail($notification);

        $item->markAsRead();

        if (in_array(
            $item->data['type'] ?? '',
            ['conference_message', 'conference_support', 'conference_support_review'],
            true
        )) {
            $requestId = $item->data['slot_request_id'] ?? null;

            if (! is_scalar($requestId) || ! ctype_digit((string) $requestId) || (int) $requestId < 1) {
                return redirect()->route('responsable-conferences.notifications.index');
            }

            $reservation = SlotRequest::query()
                ->whereKey((int) $requestId)
                ->where('status', SlotRequestStatus::Accepted)
                ->whereNotNull('assigned_slot_id')
                ->whereHas('assignedSlot', fn ($query) => $query
                    ->whereColumn('slots.exhibitor_id', 'slot_requests.exhibitor_id')
                    ->whereIn('status', ['reserved', 'paid']))
                ->first();

            return $reservation
                ? redirect()->route('responsable-conferences.supports.show', $reservation)
                : redirect()->route('responsable-conferences.notifications.index');
        }

        if (in_array(
            $item->data['type'] ?? '',
            [
                'conference_request',
                'conference_accepted',
                'conference_rejected',
            ],
            true
        )) {
            return redirect()->route(
                'responsable-conferences.requests.index',
                [
                    'status' => match ($item->data['type']) {
                        'conference_accepted' => 'accepted',
                        'conference_rejected' => 'rejected',
                        default => 'pending',
                    },
                ]
            );
        }

        return redirect()->route(
            'responsable-conferences.notifications.index'
        );
    }
}

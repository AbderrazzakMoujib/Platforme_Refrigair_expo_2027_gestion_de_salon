<?php

namespace App\Http\Controllers;

use App\Enums\SlotRequestStatus;
use App\Models\SlotRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConferenceSupportInboxController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => [
                'nullable',
                Rule::in([
                    'all',
                    'not_submitted',
                    'pending',
                    'approved',
                    'revision_requested',
                ]),
            ],
        ]);

        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? 'all';

        $base = SlotRequest::query()
            ->where('status', SlotRequestStatus::Accepted)
            ->whereNotNull('assigned_slot_id')
            ->whereHas('assignedSlot', fn ($query) => $query
                ->whereColumn('slots.exhibitor_id', 'slot_requests.exhibitor_id')
                ->whereIn('status', ['reserved', 'paid']));

        $counts = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)
                ->where('support_review_status', 'pending')
                ->count(),
            'approved' => (clone $base)
                ->where('support_review_status', 'approved')
                ->count(),
            'revision_requested' => (clone $base)
                ->where('support_review_status', 'revision_requested')
                ->count(),
        ];

        $requests = (clone $base)
            ->with([
                'exhibitor:id,company_name,logo',
                'assignedSlot.space',
            ])
            ->withCount([
                'conferenceMessages as unread_messages_count' =>
                    fn ($query) => $query
                        ->whereNull('read_at')
                        ->where(
                            'sender_id',
                            '!=',
                            $request->user()->id
                        ),
            ])
            ->when(
                $status !== 'all',
                fn ($query) => $query->where(
                    'support_review_status',
                    $status
                )
            )
            ->when(
                $search !== '',
                fn ($query) => $query->where(
                    function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhereHas(
                                'exhibitor',
                                fn ($e) => $e->where(
                                    'company_name',
                                    'like',
                                    "%{$search}%"
                                )
                            );
                    }
                )
            )
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view(
            'responsable-conferences.supports',
            compact('requests', 'counts', 'filters')
        );
    }
}

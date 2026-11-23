<?php

namespace App\Http\Controllers;

use App\Enums\SlotRequestStatus;
use App\Http\Requests\Slots\AcceptSlotRequestForm;
use App\Http\Requests\Slots\RejectSlotRequestForm;
use App\Models\Slot;
use App\Models\SlotRequest;
use App\Services\SlotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Demandes de créneaux envoyées par les exposants. */
class CommercialSlotRequestController extends Controller
{
    public function __construct(private SlotService $service) {}

    public function index(Request $request): View
    {
        Gate::authorize('manage', Slot::class);

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['all', ...array_column(SlotRequestStatus::cases(), 'value')])],
        ]);

        $status = $filters['status'] ?? SlotRequestStatus::Pending->value;

        $requests = SlotRequest::query()
            ->with([
                'exhibitor:id,company_name',
                'firstChoice.exhibitor:id,company_name',
                'secondChoice.exhibitor:id,company_name',
                'assignedSlot:id,code',
                'processor:id,name',
            ])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $counts = SlotRequest::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('commercial.slots.requests', [
            'commercial' => $request->user(),
            'commercialInitials' => $request->user()->initials,
            'requests' => $requests,
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    public function accept(AcceptSlotRequestForm $request, SlotRequest $slotRequest): RedirectResponse
    {
        $slot = $this->service->accept($slotRequest, $request->integer('slot_id'), $request->user());

        return back()->with('status', "Demande acceptée : créneau {$slot->code} attribué à {$slot->holder_name}.");
    }

    public function reject(RejectSlotRequestForm $request, SlotRequest $slotRequest): RedirectResponse
    {
        $this->service->reject($slotRequest, $request->validated('rejection_reason'), $request->user());

        return back()->with('status', 'Demande refusée.');
    }
}

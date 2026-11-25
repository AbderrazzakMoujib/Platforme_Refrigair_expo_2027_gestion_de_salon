<?php

namespace App\Http\Controllers;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Enums\SpaceType;
use App\Http\Requests\Slots\SubmitCommercialSlotRequestForm;
use App\Models\Exhibitor;
use App\Models\Slot;
use App\Models\SlotRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Demandes et suivi strictement limités aux exposants du commercial connecté. */
class CommercialSlotRequestController extends Controller
{
    public function index(Request $request): View
    {
        $commercial = $request->user();
        Gate::authorize('viewAny', SlotRequest::class);
        $status = $request->validate([
            'status' => ['nullable', Rule::in(['all', ...array_column(SlotRequestStatus::cases(), 'value')])],
        ])['status'] ?? 'all';

        $base = SlotRequest::query()->whereHas('exhibitor', fn ($query) => $query->where('commercial_id', $commercial->id));
        $requests = (clone $base)
            ->with(['exhibitor:id,company_name,commercial_id', 'firstChoice:id,code,date,start_time,end_time', 'secondChoice:id,code,date,start_time,end_time', 'assignedSlot:id,code', 'processor:id,name'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $requests->getCollection()->each(fn (SlotRequest $slotRequest) => Gate::authorize('viewOwn', $slotRequest));

        return view('commercial.slots.requests', [
            'commercial' => $commercial,
            'commercialInitials' => $commercial->initials,
            'requests' => $requests,
            'status' => $status,
            'counts' => (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
            'exhibitors' => Exhibitor::query()->where('commercial_id', $commercial->id)->orderBy('company_name')->get(['id', 'company_name']),
            'availableSlots' => Slot::query()->with('space:id,type')->where('status', SlotStatus::Available)->ordered()->get(),
            'spaceTypes' => SpaceType::cases(),
        ]);
    }

    public function store(SubmitCommercialSlotRequestForm $request): RedirectResponse
    {
        $data = $request->validated();
        $commercial = $request->user();
        $exhibitor = Exhibitor::query()
            ->where('commercial_id', $commercial->id)
            ->findOrFail($data['exhibitor_id']);
        Gate::authorize('createFor', [SlotRequest::class, $exhibitor]);

        $slotChoices = Slot::query()
            ->with('space:id,type')
            ->whereIn('id', [$data['first_choice_slot_id'], $data['second_choice_slot_id']])
            ->get()
            ->keyBy('id');

        if ($slotChoices->count() !== 2
            || $slotChoices->contains(fn (Slot $slot) => $slot->status !== SlotStatus::Available || $slot->space->type !== SpaceType::from($data['type']))) {
            throw ValidationException::withMessages([
                'first_choice_slot_id' => 'Les deux choix doivent être disponibles et appartenir au même espace demandé.',
            ]);
        }

        
$slotRequest = DB::transaction(
    fn () => $exhibitor->slotRequests()->create(
        $data + [
            'status' => SlotRequestStatus::Pending,
        ]
    )
);

app(
    \App\Services\ConferenceManagerNotificationService::class
)->newRequest($slotRequest);


        return redirect()->route('commercial.slot-requests.index')
            ->with('status', 'Votre demande a été envoyée. Vous pourrez suivre son statut dans cette page.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\SlotRequestStatus;
use App\Enums\SpaceType;
use App\Http\Requests\Slots\SubmitSlotRequestForm;
use App\Http\Requests\Slots\UploadSlotSupportForm;
use App\Models\Slot;
use App\Services\SlotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Espace exposant : demandes de créneaux, créneaux confirmés et supports. */
class ExhibitorSlotController extends Controller
{
    public function index(Request $request): View
    {
        $exhibitor = $request->user()->exhibitorProfile()->firstOrFail();

        $allSlots = Slot::query()
            ->with('space:id,type')
            ->ordered()
            ->get();

        $available = $allSlots
            ->filter(fn (Slot $slot) => ! $slot->status->isTaken())
            ->groupBy(fn (Slot $slot) => $slot->space->type->value);

        $myRequests = $exhibitor->slotRequests()
            ->with(['firstChoice:id,code,date,start_time,end_time', 'secondChoice:id,code,date,start_time,end_time', 'assignedSlot:id,code'])
            ->latest()
            ->latest('id')
            ->get();

        // Planning visuel : type d'espace → jour → créneaux.
        // Les noms des autres exposants ne sont jamais envoyés à la vue.
        $planning = collect(SpaceType::cases())->mapWithKeys(fn (SpaceType $type) => [
            $type->value => $allSlots
                ->filter(fn (Slot $slot) => $slot->space->type === $type)
                ->groupBy(fn (Slot $slot) => $slot->date->toDateString()),
        ]);

        return view('exhibitor.slots.index', [
            'user' => $request->user(),
            'exhibitor' => $exhibitor,
            'mySlots' => $exhibitor->slots()->with('space:id,type')->ordered()->get(),
            'myRequests' => $myRequests,
            'availableSlots' => $available,
            'planning' => $planning,
            'requestedSlotIds' => $myRequests
                ->where('status', SlotRequestStatus::Pending)
                ->flatMap(fn ($slotRequest) => $slotRequest->choiceIds())
                ->unique()
                ->values()
                ->all(),
            'spaceTypes' => SpaceType::cases(),
        ]);
    }

    public function store(SubmitSlotRequestForm $request): RedirectResponse
    {
        $exhibitor = $request->user()->exhibitorProfile()->firstOrFail();

        
$slotRequest = $exhibitor->slotRequests()->create(
    $request->validated() + [
        'status' => SlotRequestStatus::Pending,
    ]
);

app(
    \App\Services\ConferenceManagerNotificationService::class
)->newRequest($slotRequest);


        return redirect()
            ->route('exhibitor.slots.index')
            ->with('status', 'Votre demande a bien été envoyée. Vous recevrez une confirmation dès qu\'un créneau vous sera attribué.');
    }

    public function uploadSupport(UploadSlotSupportForm $request, Slot $slot, SlotService $service): RedirectResponse
    {
        $service->storeSupport($slot, $request->file('support'));

        return back()->with('status', "Support envoyé pour le créneau {$slot->code}.");
    }

    public function downloadSupport(Request $request, Slot $slot): StreamedResponse
    {
        Gate::authorize('downloadSupport', $slot);

        abort_unless(Storage::disk('local')->exists($slot->support_path), 404);

        return Storage::disk('local')->download(
            $slot->support_path,
            $slot->support_original_name ?? basename($slot->support_path)
        );
    }
}

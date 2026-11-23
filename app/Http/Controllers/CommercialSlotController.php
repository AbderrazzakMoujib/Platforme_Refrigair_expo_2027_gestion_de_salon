<?php

namespace App\Http\Controllers;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Http\Requests\Slots\UpdateSlotForm;
use App\Http\Requests\Slots\UploadSlotSupportForm;
use App\Models\Exhibitor;
use App\Models\Slot;
use App\Models\SlotRequest;
use App\Models\Space;
use App\Services\SlotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Planning des Conférences & Lounge des Technologies (géré par le commercial). */
class CommercialSlotController extends Controller
{
    public function __construct(private SlotService $service) {}

    public function index(Request $request): View
    {
        Gate::authorize('manage', Slot::class);

        $spaces = Space::query()->orderBy('id')->get();

        $slots = Slot::query()
            ->with(['space:id,type,name', 'exhibitor:id,company_name'])
            ->ordered()
            ->get();

        // Planning : jour → espace → créneaux.
        $planning = $slots
            ->groupBy(fn (Slot $slot) => $slot->date->toDateString())
            ->map(fn ($daySlots) => $spaces->mapWithKeys(fn (Space $space) => [
                $space->id => $daySlots->where('space_id', $space->id)->values(),
            ]));

        return view('commercial.slots.index', [
            'commercial' => $request->user(),
            'commercialInitials' => $request->user()->initials,
            'spaces' => $spaces,
            'slots' => $slots,
            'planning' => $planning,
            'statuses' => SlotStatus::cases(),
            'exhibitors' => Exhibitor::query()->orderBy('company_name')->get(['id', 'company_name']),
            'stats' => $this->service->stats(),
            'pendingRequestsCount' => SlotRequest::query()->where('status', SlotRequestStatus::Pending)->count(),
        ]);
    }

    public function update(UpdateSlotForm $request, Slot $slot): RedirectResponse
    {
        $wasFree = ! $slot->status->isTaken();

        try {
            $slot = $this->service->update($slot, $request->validated());
        } catch (ValidationException $exception) {
            throw $exception->errorBag('slot');
        }

        $message = match (true) {
            $slot->status === SlotStatus::Available => "Créneau {$slot->code} libéré.",
            $wasFree => "Créneau {$slot->code} réservé pour {$slot->holder_name} ({$slot->status->label()}).",
            default => "Créneau {$slot->code} mis à jour.",
        };

        return redirect()
            ->route('commercial.slots.index')
            ->with('status', $message);
    }

    /** Le commercial dépose le support à la place du titulaire. */
    public function uploadSupport(UploadSlotSupportForm $request, Slot $slot): RedirectResponse
    {
        $this->service->storeSupport($slot, $request->file('support'));

        return redirect()
            ->route('commercial.slots.index')
            ->with('status', "Support enregistré pour le créneau {$slot->code}.");
    }

    public function downloadSupport(Request $request, Slot $slot): StreamedResponse
    {
        Gate::authorize('downloadSupport', $slot);

        abort_unless(Storage::disk('local')->exists($slot->support_path), 404);

        return Storage::disk('local')->download(
            $slot->support_path,
            $slot->code.'-'.($slot->support_original_name ?? basename($slot->support_path))
        );
    }
}

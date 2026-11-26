<?php

namespace App\Http\Controllers;

use App\Enums\SlotRequestStatus;
use App\Http\Requests\Slots\AcceptSlotRequestForm;
use App\Http\Requests\Slots\RejectSlotRequestForm;
use App\Http\Requests\Slots\UpdateSlotForm;
use App\Http\Requests\Slots\UpdateManualReservationForm;
use App\Models\Slot;
use App\Models\SlotRequest;
use App\Models\SlotRequestDecision;
use App\Models\Space;
use App\Services\SlotService;
use App\Support\ProfilePhotoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Enums\SlotStatus;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResponsableConferenceController extends Controller
{
    


public function dashboard(): View
{
    $pendingCount = SlotRequest::query()
        ->where('status', SlotRequestStatus::Pending)
        ->count();

    $confirmedCount = SlotRequest::query()
        ->where('status', SlotRequestStatus::Accepted)
        ->count();

    $rejectedCount = SlotRequest::query()
        ->where('status', SlotRequestStatus::Rejected)
        ->count();
    $cancelledCount = SlotRequest::query()
        ->where('status', SlotRequestStatus::Cancelled)
        ->count();

    $decisionCount = SlotRequestDecision::query()->count();

    $totalSlots = Slot::query()->count();

    $slotStatusCounts = [
        'available' => Slot::query()
            ->where('status', SlotStatus::Available)
            ->count(),

        'option' => Slot::query()
            ->where('status', SlotStatus::Option)
            ->count(),

        'reserved' => Slot::query()
            ->where('status', SlotStatus::Reserved)
            ->count(),

        'paid' => Slot::query()
            ->where('status', SlotStatus::Paid)
            ->count(),
    ];

    $availableSlots = $slotStatusCounts['available'];

    $occupiedSlots = $totalSlots - $availableSlots;

    $occupancyRate = $totalSlots > 0
        ? round(($occupiedSlots / $totalSlots) * 100)
        : 0;

    $upcomingSlots = Slot::query()
        ->with([
            'space:id,type,name',
            'exhibitor:id,company_name,logo',
        ])
        ->whereDate('date', '>=', Carbon::today())
        ->ordered()
        ->limit(6)
        ->get();

    $recentRequests = SlotRequest::query()
        ->with([
            'exhibitor:id,company_name,commercial_id,logo',
            'exhibitor.commercial:id,name',
            'firstChoice:id,code,date,start_time,end_time,status',
            'secondChoice:id,code,date,start_time,end_time,status',
        ])
        ->where('status', SlotRequestStatus::Pending)
        ->latest('id')
        ->limit(5)
        ->get();

    $recentDecisions = SlotRequestDecision::query()
        ->with('assignedSlot:id,code')
        ->orderByDesc('processed_at')
        ->orderByDesc('id')
        ->limit(5)
        ->get();

    return view('responsable-conferences.dashboard', [
        'pendingCount' => $pendingCount,
        'confirmedCount' => $confirmedCount,
        'rejectedCount' => $rejectedCount,
        'cancelledCount' => $cancelledCount,
        'decisionCount' => $decisionCount,
        'totalSlots' => $totalSlots,
        'availableSlots' => $availableSlots,
        'occupiedSlots' => $occupiedSlots,
        'occupancyRate' => $occupancyRate,
        'slotStatusCounts' => $slotStatusCounts,
        'upcomingSlots' => $upcomingSlots,
        'recentRequests' => $recentRequests,
        'recentDecisions' => $recentDecisions,
    ]);
}


    
public function planning(): View
{
    $spaces = Space::query()
        ->orderBy('id')
        ->get();

    $slots = Slot::query()
        ->with([
            'space:id,type,name',
            'exhibitor:id,company_name,commercial_id,logo',
            'exhibitor.commercial:id,name',
            'commercial:id,name',
        ])
        ->ordered()
        ->get();
    $manualReservationIds = Slot::query()
        ->whereNull('commercial_id')
        ->whereIn('status', [SlotStatus::Option->value, SlotStatus::Reserved->value, SlotStatus::Paid->value])
        ->whereNotIn('id', SlotRequest::query()
            ->select('assigned_slot_id')
            ->whereNotNull('assigned_slot_id')
            ->where('status', SlotRequestStatus::Accepted->value))
        ->pluck('id')
        ->all();

    return view('responsable-conferences.planning', [
        'spaces' => $spaces,
        'slots' => $slots,
        'exhibitors' => \App\Models\Exhibitor::query()->orderBy('company_name')->get(['id', 'company_name']),
        'statuses' => SlotStatus::cases(),
        'manualReservationIds' => $manualReservationIds,
    ]);
}

public function storeReservation(UpdateSlotForm $request, SlotService $service): RedirectResponse
{
    Gate::authorize('reserve', Slot::class);
    $slot = Slot::query()->findOrFail($request->validated('slot_id'));

    try {
        $slot = $service->reserveAvailable($slot, $request->validated(), $request->user());
    } catch (ValidationException $exception) {
        throw $exception->errorBag('slot');
    }

    return redirect()
        ->route('responsable-conferences.planning')
        ->with('status', "Créneau {$slot->code} réservé pour {$slot->holder_name} ({$slot->status->label()}).");
}

public function updateReservation(UpdateManualReservationForm $request, Slot $slot, SlotService $service): RedirectResponse
{
    $service->updateManualReservation($slot, $request->validated(), $request->user());

    return redirect()->route('responsable-conferences.planning')->with('status', 'La réservation a été mise à jour.');
}

public function destroyReservation(Request $request, Slot $slot, SlotService $service): RedirectResponse
{
    Gate::authorize('manageManualReservation', $slot);
    $service->releaseManualReservation($slot, $request->user());

    return redirect()->route('responsable-conferences.planning')->with('status', 'La réservation a été supprimée et le créneau est de nouveau disponible.');
}


    
public function requests(Request $request): View
{
    Gate::authorize('viewAny', SlotRequest::class);

    $filters = $request->validate([
        'status' => [
            'nullable',
            Rule::in(['pending', 'accepted', 'rejected', 'cancelled', 'all']),
        ],
        'search' => ['nullable', 'string', 'max:150'],
        'type' => [
            'nullable',
            Rule::in(['conference', 'lounge']),
        ],
        'space_id' => [
            'nullable',
            'integer',
            'exists:spaces,id',
        ],
        'period' => [
            'nullable',
            Rule::in(['all', '7d', '30d']),
        ],
    ]);

    $status = $filters['status'] ?? 'pending';
    $search = trim($filters['search'] ?? '');
    $type = $filters['type'] ?? '';
    $spaceId = $filters['space_id'] ?? null;
    $period = $filters['period'] ?? 'all';

    // Statistiques globales, indépendantes des filtres.
    $statusCounts = SlotRequest::query()
        ->selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status');

    $pendingCount = (int) ($statusCounts['pending'] ?? 0);
    $acceptedCount = (int) ($statusCounts['accepted'] ?? 0);
    $rejectedCount = (int) ($statusCounts['rejected'] ?? 0);
    $cancelledCount = (int) ($statusCounts['cancelled'] ?? 0);

    $totalCount = $pendingCount
        + $acceptedCount
        + $rejectedCount
        + $cancelledCount;

    $spaces = Space::query()
        ->orderBy('name')
        ->get();

    $query = SlotRequest::query()
        ->with([
            'exhibitor:id,company_name,commercial_id,logo',
            'exhibitor.commercial:id,name',
            'firstChoice:id,code,date,start_time,end_time,status,space_id',
            'firstChoice.space:id,name,type',
            'secondChoice:id,code,date,start_time,end_time,status,space_id',
            'secondChoice.space:id,name,type',
            'assignedSlot:id,code',
            'processor:id,name',
        ]);

    if ($status !== 'all') {
        $query->where('status', $status);
    }

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('summary', 'like', "%{$search}%")
                ->orWhere('speakers', 'like', "%{$search}%")
                ->orWhereHas('exhibitor', function ($exhibitor) use ($search) {
                    $exhibitor->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%");
                });
        });
    }

    if ($type !== '') {
        $query->where('type', $type);
    }

    if ($spaceId) {
        $query->where(function ($q) use ($spaceId) {
            $q->whereHas('firstChoice', function ($slot) use ($spaceId) {
                $slot->where('space_id', $spaceId);
            })->orWhereHas('secondChoice', function ($slot) use ($spaceId) {
                $slot->where('space_id', $spaceId);
            });
        });
    }

    if ($period === '7d') {
        $query->where('created_at', '>=', now()->subDays(7));
    } elseif ($period === '30d') {
        $query->where('created_at', '>=', now()->subDays(30));
    }

    $requests = $query
        ->latest('id')
        ->paginate(10)
        ->withQueryString();

    return view('responsable-conferences.requests', [
        'requests' => $requests,
        'status' => $status,
        'search' => $search,
        'type' => $type,
        'spaceId' => $spaceId,
        'period' => $period,
        'spaces' => $spaces,
        'pendingCount' => $pendingCount,
        'acceptedCount' => $acceptedCount,
        'rejectedCount' => $rejectedCount,
        'cancelledCount' => $cancelledCount,
        'totalCount' => $totalCount,
    ]);
}


    
public function history(Request $request): View
{
    Gate::authorize('viewAny', SlotRequest::class);

    $filters = $this->historyFilters($request);

    $baseQuery = SlotRequestDecision::query();

    $totalDecisions = (clone $baseQuery)->count();

    $acceptedCount = (clone $baseQuery)
        ->where('decision', SlotRequestStatus::Accepted->value)
        ->count();

    $rejectedCount = (clone $baseQuery)
        ->where('decision', SlotRequestStatus::Rejected->value)
        ->count();

    $cancelledCount = (clone $baseQuery)
        ->where('decision', SlotRequestStatus::Cancelled->value)
        ->count();

    $firstDecision = (clone $baseQuery)->min('processed_at');
    $lastDecision = (clone $baseQuery)->max('processed_at');

    $periodLabel = $firstDecision && $lastDecision
        ? \Illuminate\Support\Carbon::parse($firstDecision)
            ->locale('fr')->isoFormat('MMM YYYY')
            .' – '.
            \Illuminate\Support\Carbon::parse($lastDecision)
            ->locale('fr')->isoFormat('MMM YYYY')
        : 'Aucune décision';

    $spaces = Space::query()
        ->orderBy('name')
        ->get();

    $decisions = $this->historyQuery($filters)
        ->with([
            'request.exhibitor',
            'request.exhibitor.commercial',
            'assignedSlot.space',
            'assignedSlot.exhibitor',
            'assignedSlot.commercial',
            'processor:id,name',
        ])
        ->orderByDesc('processed_at')
        ->orderByDesc('id')
        ->paginate(10)
        ->withQueryString();

    return view('responsable-conferences.history', [
        'decisions' => $decisions,
        'spaces' => $spaces,
        'filters' => $filters,
        'totalDecisions' => $totalDecisions,
        'acceptedCount' => $acceptedCount,
        'rejectedCount' => $rejectedCount,
        'cancelledCount' => $cancelledCount,
        'periodLabel' => $periodLabel,
    ]);
}

public function exportHistory(Request $request): StreamedResponse
{
    Gate::authorize('viewAny', SlotRequest::class);

    $filters = $this->historyFilters($request);

    $filename = 'refrigair-historique-decisions-'
        . now()->format('Ymd-His') . '.csv';

    return response()->streamDownload(
        function () use ($filters) {
            $output = fopen('php://output', 'w');

            // Compatibilité Excel UTF-8.
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'Référence',
                'Entreprise',
                'Sujet',
                'Type',
                'Décision',
                'Espace',
                'Créneau',
                'Date du créneau',
                'Commercial',
                'Traité par',
                'Date de décision',
                'Motif',
            ], ';');

            $this->historyQuery($filters)
                ->with([
                    'request.exhibitor.commercial',
                    'assignedSlot.space',
                ])
                ->chunkById(200, function ($items) use ($output) {

                    foreach ($items as $decision) {
                        $slot = $decision->assignedSlot;

                        $commercial = $decision->request
                            ?->exhibitor
                            ?->commercial
                            ?->name ?? '';

                        $values = [
                            'DR-' . $decision->slot_request_id,
                            $decision->exhibitor_name,
                            $decision->request_title,
                            $decision->request_type,
                            match ($decision->decision) {
                                'accepted' => 'Validée',
                                'cancelled' => 'Annulée',
                                default => 'Refusée',
                            },
                            $slot?->space?->name
                                ?? $slot?->space?->type?->label(),
                            $slot?->code,
                            $slot?->date?->format('d/m/Y'),
                            $commercial,
                            $decision->processor_name,
                            $decision->processed_at?->format('d/m/Y H:i'),
                            $decision->reason,
                        ];

                        // Protection contre les formules Excel.
                        $safeValues = array_map(
                            function ($value) {
                                $value = (string) ($value ?? '');

                                if (preg_match(
                                    '/^[\s\x00-\x1F]*[=+\-@]/u',
                                    $value
                                )) {
                                    return "'" . $value;
                                }

                                return $value;
                            },
                            $values
                        );

                        fputcsv($output, $safeValues, ';');
                    }
                });

            fclose($output);
        },
        $filename,
        ['Content-Type' => 'text/csv; charset=UTF-8']
    );
}

private function historyFilters(Request $request): array
{
    return $request->validate([
        'search' => ['nullable', 'string', 'max:150'],
        'decision' => [
            'nullable',
            Rule::in(['accepted', 'rejected', 'cancelled']),
        ],
        'type' => [
            'nullable',
            Rule::in(['conference', 'lounge']),
        ],
        'space_id' => [
            'nullable',
            'integer',
            'exists:spaces,id',
        ],
        'period' => [
            'nullable',
            Rule::in(['all', '7d', '30d']),
        ],
    ]);
}

private function historyQuery(array $filters): Builder
{
    $query = SlotRequestDecision::query();

    $search = trim($filters['search'] ?? '');

    if ($search !== '') {
        $query->where(function (Builder $q) use ($search) {
            $q->where('exhibitor_name', 'like', "%{$search}%")
                ->orWhere('request_title', 'like', "%{$search}%")
                ->orWhere('processor_name', 'like', "%{$search}%")
                ->orWhere('reason', 'like', "%{$search}%")
                ->orWhereHas(
                    'request.exhibitor.commercial',
                    fn ($commercial) => $commercial
                        ->where('name', 'like', "%{$search}%")
                );
        });
    }

    if (!empty($filters['decision'])) {
        $query->where('decision', $filters['decision']);
    }

    if (!empty($filters['type'])) {
        $query->where('request_type', $filters['type']);
    }

    if (!empty($filters['space_id'])) {
        $spaceId = (int) $filters['space_id'];

        // Décision confirmée : créneau attribué.
        // Décision refusée : choix initialement demandés.
        $query->where(function (Builder $q) use ($spaceId) {
            $q->whereHas(
                'assignedSlot',
                fn ($slot) => $slot->where('space_id', $spaceId)
            )
            ->orWhereHas(
                'request.firstChoice',
                fn ($slot) => $slot->where('space_id', $spaceId)
            )
            ->orWhereHas(
                'request.secondChoice',
                fn ($slot) => $slot->where('space_id', $spaceId)
            );
        });
    }

    $period = $filters['period'] ?? 'all';

    if ($period === '7d') {
        $query->where('processed_at', '>=', now()->subDays(7));
    }

    if ($period === '30d') {
        $query->where('processed_at', '>=', now()->subDays(30));
    }

    return $query;
}


    
public function confirmed(Request $request): View
{
    Gate::authorize('viewAny', SlotRequest::class);

    $filters = $this->confirmedFilters($request);

    $baseQuery = SlotRequestDecision::query()
        ->where('decision', SlotRequestStatus::Accepted->value);

    $totalConfirmed = (clone $baseQuery)->count();

    $conferenceCount = (clone $baseQuery)
        ->where('request_type', 'conference')
        ->count();

    $loungeCount = (clone $baseQuery)
        ->where('request_type', 'lounge')
        ->count();

    $companiesCount = (clone $baseQuery)
        ->whereNotNull('exhibitor_id')
        ->distinct()
        ->count('exhibitor_id');

    $spaces = Space::query()
        ->orderBy('name')
        ->get();

    $confirmations = $this->confirmedQuery($filters)
        ->with([
            'request.exhibitor',
            'request.exhibitor.commercial',
            'assignedSlot.space',
            'assignedSlot.exhibitor',
            'assignedSlot.exhibitor.commercial',
            'assignedSlot.commercial',
        ])
        ->orderByDesc('processed_at')
        ->orderByDesc('id')
        ->paginate(10)
        ->withQueryString();

    return view('responsable-conferences.confirmed', [
        'confirmations' => $confirmations,
        'spaces' => $spaces,
        'filters' => $filters,
        'totalConfirmed' => $totalConfirmed,
        'conferenceCount' => $conferenceCount,
        'loungeCount' => $loungeCount,
        'companiesCount' => $companiesCount,
    ]);
}

public function exportConfirmed(Request $request): StreamedResponse
{
    Gate::authorize('viewAny', SlotRequest::class);

    $filters = $this->confirmedFilters($request);

    $filename = 'refrigair-reservations-confirmees-'
        . now()->format('Ymd-His') . '.csv';

    return response()->streamDownload(
        function () use ($filters) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM pour Excel.
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'Référence',
                'Entreprise',
                'Sujet',
                'Type',
                'Espace',
                'Code créneau',
                'Date conférence',
                'Début',
                'Fin',
                'Commercial',
                'Validé par',
                'Date validation',
            ], ';');

            $this->confirmedQuery($filters)
                ->with([
                    'request.exhibitor.commercial',
                    'assignedSlot.space',
                    'assignedSlot.commercial',
                ])
                ->orderBy('id')
                ->chunkById(200, function ($items) use ($output) {
                    foreach ($items as $item) {
                        $slot = $item->assignedSlot;

                        $commercial = $item->request
                            ?->exhibitor
                            ?->commercial
                            ?->name
                            ?? $slot?->commercial?->name
                            ?? '';

                        $values = [
                            'RC-' . $item->id,
                            $item->exhibitor_name,
                            $item->request_title,
                            $item->request_type,
                            $slot?->space?->name
                                ?? $slot?->space?->type?->label(),
                            $slot?->code,
                            $slot?->date?->format('d/m/Y'),
                            $slot?->start_label,
                            $slot?->end_label,
                            $commercial,
                            $item->processor_name,
                            $item->processed_at?->format('d/m/Y H:i'),
                        ];

                        // Évite l'interprétation de formules dans Excel.
                        $safeValues = array_map(
                            function ($value) {
                                $value = (string) ($value ?? '');

                                if (preg_match(
                                    '/^[\s\x00-\x1F]*[=+\-@]/u',
                                    $value
                                )) {
                                    return "'" . $value;
                                }

                                return $value;
                            },
                            $values
                        );

                        fputcsv($output, $safeValues, ';');
                    }
                });

            fclose($output);
        },
        $filename,
        ['Content-Type' => 'text/csv; charset=UTF-8']
    );
}

private function confirmedFilters(Request $request): array
{
    return $request->validate([
        'search' => ['nullable', 'string', 'max:150'],
        'type' => [
            'nullable',
            Rule::in(['conference', 'lounge']),
        ],
        'space_id' => [
            'nullable',
            'integer',
            'exists:spaces,id',
        ],
        'period' => [
            'nullable',
            Rule::in(['all', '7d', '30d']),
        ],
    ]);
}

private function confirmedQuery(array $filters): Builder
{
    $query = SlotRequestDecision::query()
        ->where('decision', SlotRequestStatus::Accepted->value);

    $search = trim($filters['search'] ?? '');

    if ($search !== '') {
        $query->where(function (Builder $q) use ($search) {
            $q->where('exhibitor_name', 'like', "%{$search}%")
                ->orWhere(
                    'request_title',
                    'like',
                    "%{$search}%"
                )
                ->orWhere('processor_name', 'like', "%{$search}%")
                ->orWhereHas(
                    'request.exhibitor.commercial',
                    fn ($commercial) => $commercial
                        ->where('name', 'like', "%{$search}%")
                );
        });
    }

    if (!empty($filters['type'])) {
        $query->where('request_type', $filters['type']);
    }

    if (!empty($filters['space_id'])) {
        $spaceId = (int) $filters['space_id'];

        $query->whereHas(
            'assignedSlot',
            fn ($slot) => $slot->where('space_id', $spaceId)
        );
    }

    $period = $filters['period'] ?? 'all';

    if ($period === '7d') {
        $query->where(
            'processed_at',
            '>=',
            now()->subDays(7)
        );
    }

    if ($period === '30d') {
        $query->where(
            'processed_at',
            '>=',
            now()->subDays(30)
        );
    }

    return $query;
}


    public function accept(AcceptSlotRequestForm $request, SlotRequest $slotRequest, SlotService $service): RedirectResponse
    {
        try {
            $slot = $service->accept($slotRequest, $request->integer('slot_id'), $request->user());
            app(
    \App\Services\ConferenceManagerNotificationService::class
)->accepted($slotRequest->fresh());
        } catch (ValidationException $exception) {
            throw $exception->errorBag('slot-request');
        }

        return back()->with('status', "Demande confirmée sur le créneau {$slot->code}.");
    }

    public function reject(RejectSlotRequestForm $request, SlotRequest $slotRequest, SlotService $service): RedirectResponse
    {
        try {
            $service->reject($slotRequest, $request->validated('rejection_reason'), $request->user());
            app(
    \App\Services\ConferenceManagerNotificationService::class
)->rejected($slotRequest->fresh());
        } catch (ValidationException $exception) {
            throw $exception->errorBag('slot-request');
        }

        return back()->with('status', 'La demande a été refusée.');
    }

    public function cancelRequest(Request $request, SlotRequest $slotRequest, SlotService $service): RedirectResponse
    {
        Gate::authorize('cancel', $slotRequest);
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:1000']]);
        $wasAccepted = $slotRequest->status === SlotRequestStatus::Accepted;

        $service->cancelRequest($slotRequest, $request->user(), $data['reason'] ?? null);

        return back()->with('status', $wasAccepted
            ? 'La réservation a été annulée, son créneau libéré et la décision conservée dans l’historique.'
            : 'La demande et sa conversation ont été supprimées. Les décisions déjà archivées restent dans l’historique.');
    }

    public function profile(Request $request): View
    {
        return view('responsable-conferences.profile', ['user' => $request->user()]);
    }

    
public function updateProfile(Request $request): RedirectResponse
{
    $user = $request->user();

    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => [
            'required',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($user->id),
        ],
        'phone' => ['nullable', 'string', 'max:30'],
        'profile_photo' => [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
        ],
        'remove_profile_photo' => ['nullable', 'boolean'],
    ]);

    $previousPhoto = $user->profile_photo;

    $newPhoto = $request->hasFile('profile_photo')
        ? ProfilePhotoStorage::store(
            $request->file('profile_photo')
        )
        : null;

    $user->name = $data['name'];
    $user->email = $data['email'];
    $user->phone = $data['phone'] ?? null;

    if ($newPhoto) {
        $user->profile_photo = $newPhoto;
    } elseif ($request->boolean('remove_profile_photo')) {
        $user->profile_photo = null;
    }

    try {
        if (! $user->save()) {
            throw new \RuntimeException(
                'Impossible de mettre à jour le profil.'
            );
        }
    } catch (\Throwable $exception) {
        if ($newPhoto) {
            Storage::disk('public')->delete($newPhoto);
        }

        throw $exception;
    }

    if ($previousPhoto !== $user->profile_photo) {
        ProfilePhotoStorage::deleteIfUnreferenced(
            $previousPhoto,
            $user
        );
    }

    return back()->with(
        'status',
        'Vos informations ont été mises à jour.'
    );
}


public function updatePassword(Request $request): RedirectResponse
{
    $user = $request->user();

    $data = $request->validateWithBag('passwordUpdate', [
        'current_password' => [
            'required',
            'current_password',
        ],
        'password' => [
            'required',
            'confirmed',
            Password::min(8),
        ],
    ]);

    $user->password = Hash::make($data['password']);
    $user->save();

    return back()->with(
        'password_status',
        'Votre mot de passe a été modifié avec succès.'
    );
}


}

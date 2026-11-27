<?php

namespace App\Http\Controllers;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\Exhibitor;
use App\Models\Slot;
use App\Models\SlotRequest;
use App\Models\Space;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TechnicalDashboardController extends Controller
{
    private const EQUIPMENT_CATEGORIES = [
        'Mobilier',
        'Électricité',
        'Audiovisuel',
        'Informatique',
        'Restauration',
        'Nettoyage',
        'Autres',
    ];

    public function dashboard(): View
    {
        $equipmentCategories = Equipment::query()
            ->selectRaw("COALESCE(NULLIF(category, ''), 'Sans catégorie') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $categoryColors = ['#2563EB', '#10B981', '#F59E0B', '#8B5CF6', '#06B6D4', '#F97316', '#64748B'];
        $categoryTotal = (int) $equipmentCategories->sum('total');
        $equipmentCategories = $equipmentCategories->values()->map(function ($category, int $index) use ($categoryColors, $categoryTotal): array {
            return [
                'label' => $category->label,
                'total' => (int) $category->total,
                'color' => $categoryColors[$index % count($categoryColors)],
                'percent' => $categoryTotal > 0 ? ((int) $category->total / $categoryTotal) * 100 : 0,
            ];
        });

        $directReservations = Slot::query()
            ->where('status', '!=', SlotStatus::Available->value)
            ->whereDoesntHave('assignedRequests', fn ($query) => $query->where('status', SlotRequestStatus::Accepted->value))
            ->where(function ($query): void {
                $query->whereHas('creator', fn ($creator) => $creator->where('role', UserRole::ResponsableConferences))
                    ->orWhere(fn ($legacy) => $legacy->whereNull('created_by')->whereNull('commercial_id'));
            })
            ->count();

        return view('responsable-technique.dashboard', [
            'equipmentCategories' => $equipmentCategories,
            'latestEquipment' => Equipment::query()
                ->latest('created_at')
                ->latest('id')
                ->limit(6)
                ->get(),
            'commercialPreview' => User::query()
                ->where('role', UserRole::Commercial)
                ->where('is_active', true)
                ->withCount('exhibitors')
                ->orderByDesc('exhibitors_count')
                ->orderBy('name')
                ->limit(5)
                ->get(),
            'stats' => [
                'equipment_total' => Equipment::query()->count(),
                'equipment_active' => Equipment::query()->where('is_active', true)->count(),
                'available_quantity' => (int) Equipment::query()->where('is_active', true)->sum('quantity_available'),
                'active_commercials' => User::query()->where('role', UserRole::Commercial)->where('is_active', true)->count(),
                'exhibitors' => Exhibitor::query()->count(),
                // Count occupied slot rows once, rather than adding request rows to reservations.
                'confirmed_reservations' => Slot::query()
                    ->whereIn('status', [SlotStatus::Reserved->value, SlotStatus::Paid->value])
                    ->count(),
                'pending_requests' => SlotRequest::query()->where('status', SlotRequestStatus::Pending->value)->count(),
                'available_slots' => Slot::query()->where('status', SlotStatus::Available->value)->count(),
                'direct_reservations' => $directReservations,
            ],
        ]);
    }

    public function equipment(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100', Rule::exists('equipment', 'category')],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'all'])],
            'sort' => ['nullable', Rule::in(['name_asc', 'name_desc', 'newest', 'price_asc', 'price_desc'])],
            'view' => ['nullable', Rule::in(['grid', 'list'])],
            'per_page' => ['nullable', 'integer', Rule::in([6, 12, 18, 24])],
        ]);

        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? 'all';
        $sort = $filters['sort'] ?? 'name_asc';
        $viewMode = $filters['view'] ?? 'grid';
        $perPage = (int) ($filters['per_page'] ?? 6);

        $query = Equipment::query()
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when(isset($filters['category']), fn ($query) => $query->where('category', $filters['category']))
            ->when($status !== 'all', fn ($query) => $query->where('is_active', $status === 'active'));

        match ($sort) {
            'name_desc' => $query->orderByDesc('name')->orderByDesc('id'),
            'newest' => $query->latest('created_at')->latest('id'),
            'price_asc' => $query->orderBy('price')->orderBy('name'),
            'price_desc' => $query->orderByDesc('price')->orderBy('name'),
            default => $query->orderBy('name')->orderBy('id'),
        };

        $equipment = $query->paginate($perPage)->withQueryString();

        $totalCategories = Equipment::query()
            ->whereNotNull('category')
            ->whereRaw("TRIM(category) <> ''")
            ->distinct('category')
            ->count('category');

        return view('responsable-technique.equipment', [
            'equipment' => $equipment,
            'categories' => Equipment::query()->whereNotNull('category')->whereRaw("TRIM(category) <> ''")->distinct()->orderBy('category')->pluck('category'),
            'filters' => $filters + ['status' => $status, 'search' => $search, 'sort' => $sort, 'view' => $viewMode, 'per_page' => $perPage],
            'stats' => [
                'total' => Equipment::query()->count(),
                'active' => Equipment::query()->where('is_active', true)->count(),
                'quantity' => (int) Equipment::query()->sum('quantity_available'),
                'categories' => $totalCategories,
            ],
        ]);
    }

    public function commercials(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'all'])],
            'selected' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'tab' => ['nullable', Rule::in(['exhibitors', 'activity'])],
        ]);
        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? 'all';
        $totalCommercials = User::query()->where('role', UserRole::Commercial)->count();
        $associatedExhibitors = Exhibitor::query()->whereNotNull('commercial_id')->count();

        $commercials = User::query()
            ->where('role', UserRole::Commercial)
            ->withCount('exhibitors')
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($status !== 'all', fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $selectedCommercial = null;
        $selectedExhibitors = null;
        $selectedLatestExhibitorAt = null;
        $selectedNewExhibitorsCount = 0;

        if (isset($filters['selected'])) {
            $selectedCommercial = User::query()
                ->where('role', UserRole::Commercial)
                ->withCount('exhibitors')
                ->findOrFail($filters['selected']);

            $selectedLatestExhibitorAt = $selectedCommercial->exhibitors()
                ->latest('created_at')
                ->value('created_at');
            $selectedNewExhibitorsCount = $selectedCommercial->exhibitors()
                ->where('created_at', '>=', now()->subDays(30))
                ->count();
            $selectedExhibitors = $selectedCommercial->exhibitors()
                ->with('user:id,is_active')
                ->orderBy('company_name')
                ->paginate(5, ['*'], 'exhibitors_page')
                ->withQueryString();
        }

        return view('responsable-technique.commercials', [
            'commercials' => $commercials,
            'selectedCommercial' => $selectedCommercial,
            'selectedExhibitors' => $selectedExhibitors,
            'selectedLatestExhibitorAt' => $selectedLatestExhibitorAt,
            'selectedNewExhibitorsCount' => $selectedNewExhibitorsCount,
            'selectedTab' => $filters['tab'] ?? 'exhibitors',
            'filters' => $filters + ['status' => $status, 'search' => $search],
            'stats' => [
                'active' => User::query()->where('role', UserRole::Commercial)->where('is_active', true)->count(),
                'exhibitors' => $associatedExhibitors,
                'average_exhibitors' => $totalCommercials > 0 ? $associatedExhibitors / $totalCommercials : 0,
                'new_exhibitors_30_days' => Exhibitor::query()
                    ->whereNotNull('commercial_id')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count(),
            ],
        ]);
    }

    public function commercial(User $commercial): View
    {
        abort_unless($commercial->role === UserRole::Commercial, 404);
        $commercial->loadCount('exhibitors');

        $exhibitors = $commercial->exhibitors()
            ->with('user:id,is_active')
            ->orderBy('company_name')
            ->paginate(10);

        return view('responsable-technique.commercial', [
            'commercial' => $commercial,
            'exhibitors' => $exhibitors,
            'latestExhibitorAt' => $commercial->exhibitors()->latest('created_at')->value('created_at'),
            'newExhibitorsCount' => $commercial->exhibitors()->where('created_at', '>=', now()->subDays(30))->count(),
        ]);
    }

    public function conferences(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'space' => ['nullable', 'integer', Rule::exists('spaces', 'id')],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', Rule::in(array_merge(array_map(fn (SlotStatus $status) => $status->value, SlotStatus::cases()), ['all']))],
            'tab' => ['nullable', Rule::in(['planning', 'requests', 'reservations', 'available'])],
            'mode' => ['nullable', Rule::in(['day', 'week', 'list'])],
            'request_status' => ['nullable', Rule::in(['all', 'pending', 'accepted', 'rejected', 'cancelled'])],
            'selected' => ['nullable', 'integer', Rule::exists('slots', 'id')],
        ]);

        $allSlots = Slot::query()
            ->with([
                'space:id,type,name',
                'exhibitor:id,company_name,stand_number,commercial_id',
                'commercial:id,name',
                'creator:id,name,role',
                'assignedRequests' => fn ($query) => $query->where('status', SlotRequestStatus::Accepted->value),
            ])
            ->ordered()
            ->get();

        $acceptedRequests = SlotRequest::query()
            ->with([
                'exhibitor:id,company_name,stand_number,commercial_id',
                'exhibitor.commercial:id,name',
                'firstChoice:id,code,date,start_time,end_time,space_id',
                'firstChoice.space:id,name,type',
                'secondChoice:id,code,date,start_time,end_time,space_id',
                'secondChoice.space:id,name,type',
                'assignedSlot:id,code,date,start_time,end_time,space_id,status',
                'assignedSlot.space:id,name,type',
            ])
            ->where('status', SlotRequestStatus::Accepted->value)
            ->latest('processed_at')
            ->get();

        $pendingRequests = SlotRequest::query()
            ->with([
                'exhibitor:id,company_name,stand_number,commercial_id',
                'exhibitor.commercial:id,name',
                'firstChoice:id,code,date,start_time,end_time,space_id,status',
                'firstChoice.space:id,name,type',
                'secondChoice:id,code,date,start_time,end_time,space_id,status',
                'secondChoice.space:id,name,type',
            ])
            ->where('status', SlotRequestStatus::Pending->value)
            ->latest('id')
            ->get();

        $otherRequests = SlotRequest::query()
            ->with([
                'exhibitor:id,company_name,stand_number,commercial_id',
                'exhibitor.commercial:id,name',
                'firstChoice:id,code,date,start_time,end_time,space_id,status',
                'firstChoice.space:id,name,type',
                'secondChoice:id,code,date,start_time,end_time,space_id,status',
                'secondChoice.space:id,name,type',
                'assignedSlot:id,code,date,start_time,end_time,space_id,status',
                'assignedSlot.space:id,name,type',
            ])
            ->whereIn('status', [SlotRequestStatus::Rejected->value, SlotRequestStatus::Cancelled->value])
            ->latest('id')
            ->get();

        $requestHistory = $otherRequests;

        $directReservations = $allSlots->filter(function (Slot $slot): bool {
            if (! $slot->status->isTaken() || $slot->assignedRequests->isNotEmpty()) {
                return false;
            }

            // New records have an explicit creator. Older manager-created slots
            // predate this audit column and are inferred only when unassigned to a Commercial.
            return $slot->creator?->role === UserRole::ResponsableConferences
                // Before created_by was added, only the conference manager
                // could make direct bookings without a commercial/request link.
                || ($slot->created_by === null && $slot->commercial_id === null);
        })->values();

        $slotCounts = collect(SlotStatus::cases())->mapWithKeys(fn (SlotStatus $status): array => [
            $status->value => $allSlots->where('status', $status)->count(),
        ]);

        $spaces = Space::query()->orderBy('id')->get();

        $search = mb_strtolower(trim($filters['search'] ?? ''));
        $scheduleSlots = $allSlots->filter(function (Slot $slot) use ($filters, $search): bool {
            if (isset($filters['space']) && (int) $filters['space'] !== (int) $slot->space_id) {
                return false;
            }
            if (isset($filters['status']) && $filters['status'] !== 'all' && $filters['status'] !== $slot->status->value) {
                return false;
            }
            if ($search !== '') {
                $haystack = mb_strtolower(implode(' ', [
                    $slot->code,
                    $slot->space?->name,
                    $slot->holder_name,
                    $slot->contact_name,
                    $slot->contact_email,
                    $slot->title,
                    $slot->speakers,
                ]));
                if (! str_contains($haystack, $search)) {
                    return false;
                }
            }

            return true;
        })->values();

        $dateValues = $allSlots->pluck('date')->map(fn ($date) => $date->toDateString())->unique()->sort()->values();
        $requestedDate = $filters['date'] ?? null;
        $selectedDate = $requestedDate !== null && $dateValues->contains($requestedDate)
            ? $requestedDate
            : $dateValues->first();
        $dateIndex = $selectedDate !== null ? $dateValues->search($selectedDate) : false;
        $previousDate = $dateIndex !== false && $dateIndex > 0 ? $dateValues[$dateIndex - 1] : null;
        $nextDate = $dateIndex !== false && $dateIndex < $dateValues->count() - 1 ? $dateValues[$dateIndex + 1] : null;
        $mode = $filters['mode'] ?? 'day';
        $tab = $filters['tab'] ?? 'planning';
        $requestStatus = $filters['request_status'] ?? 'all';

        $visibleDates = $mode === 'week'
            ? $dateValues
            : collect($selectedDate ? [$selectedDate] : []);

        $spacesForCalendar = isset($filters['space'])
            ? $spaces->where('id', (int) $filters['space'])->values()
            : $spaces;
        $daySchedules = $visibleDates->map(function (string $date) use ($scheduleSlots, $spacesForCalendar): array {
            $daySlots = $scheduleSlots->filter(fn (Slot $slot): bool => $slot->date->toDateString() === $date)->values();
            $spaceIds = $spacesForCalendar->pluck('id')->map(fn ($id) => (int) $id);
            $daySpaces = $spacesForCalendar->filter(fn (Space $space): bool => $spaceIds->contains((int) $space->id))->values();

            if ($daySlots->isEmpty()) {
                return ['date' => $date, 'slots' => $daySlots, 'spaces' => $daySpaces, 'hours' => collect(), 'start_hour' => null, 'end_hour' => null, 'height' => 0];
            }

            $startMinutes = $daySlots->map(fn (Slot $slot): int => ((int) substr($slot->start_time, 0, 2) * 60) + (int) substr($slot->start_time, 3, 2));
            $endMinutes = $daySlots->map(fn (Slot $slot): int => ((int) substr($slot->end_time, 0, 2) * 60) + (int) substr($slot->end_time, 3, 2));
            $startHour = (int) floor($startMinutes->min() / 60);
            $endHour = (int) ceil($endMinutes->max() / 60);
            $endHour = max($startHour + 1, $endHour);

            return [
                'date' => $date,
                'slots' => $daySlots,
                'spaces' => $daySpaces,
                'hours' => collect(range($startHour, $endHour - 1)),
                'start_hour' => $startHour,
                'end_hour' => $endHour,
                'height' => ($endHour - $startHour) * 88,
            ];
        })->values();

        $tabSlots = $mode === 'week'
            ? $scheduleSlots
            : $scheduleSlots->filter(fn (Slot $slot): bool => $selectedDate === null || $slot->date->toDateString() === $selectedDate)->values();
        $reservationSlots = $tabSlots->filter(fn (Slot $slot): bool => $slot->status->isSold())->values();
        $availableSlots = $tabSlots->where('status', SlotStatus::Available)->values();

        $requestSearch = $search;
        $visibleRequests = $pendingRequests->concat($acceptedRequests)->concat($requestHistory)->filter(function (SlotRequest $slotRequest) use ($requestStatus, $requestSearch): bool {
            if ($requestStatus !== 'all' && $slotRequest->status->value !== $requestStatus) {
                return false;
            }
            if ($requestSearch !== '') {
                $text = mb_strtolower(implode(' ', [
                    $slotRequest->title,
                    $slotRequest->speakers,
                    $slotRequest->exhibitor?->company_name,
                    $slotRequest->exhibitor?->commercial?->name,
                ]));

                return str_contains($text, $requestSearch);
            }

            return true;
        })->sortByDesc('id')->values();
        $visiblePendingRequests = $visibleRequests->where('status', SlotRequestStatus::Pending)->values();
        $visibleAcceptedRequests = $visibleRequests->where('status', SlotRequestStatus::Accepted)->values();
        $visibleOtherRequests = $visibleRequests->whereIn('status', [SlotRequestStatus::Rejected, SlotRequestStatus::Cancelled])->values();

        $selectedSlot = isset($filters['selected'])
            ? $allSlots->firstWhere('id', (int) $filters['selected'])
            : null;
        $selectedSlotDetails = null;

        if ($selectedSlot) {
            $selectedStart = ((int) substr($selectedSlot->start_time, 0, 2) * 60) + (int) substr($selectedSlot->start_time, 3, 2);
            $selectedEnd = ((int) substr($selectedSlot->end_time, 0, 2) * 60) + (int) substr($selectedSlot->end_time, 3, 2);
            $duration = max(0, $selectedEnd - $selectedStart);
            $isDirect = $directReservations->contains('id', $selectedSlot->id);
            $hasAcceptedRequest = $selectedSlot->assignedRequests->isNotEmpty();
            $origin = match (true) {
                $hasAcceptedRequest => 'Demande exposant acceptée',
                $isDirect && $selectedSlot->created_by !== null => 'Réservation directe',
                $isDirect => 'Réservation directe (historique)',
                $selectedSlot->commercial_id !== null => 'Réservation commerciale',
                $selectedSlot->status->isTaken() => 'Origine non enregistrée',
                default => 'Créneau disponible',
            };
            $selectedSlotDetails = [
                'origin' => $origin,
                'duration' => intdiv($duration, 60).' h'.($duration % 60 ? ' '.($duration % 60).' min' : ''),
                'is_direct' => $isDirect,
                'commercial' => $selectedSlot->commercial,
            ];
        }

        return view('responsable-technique.conferences', [
            'spaces' => $spaces,
            'dates' => $dateValues,
            'selectedDate' => $selectedDate,
            'previousDate' => $previousDate,
            'nextDate' => $nextDate,
            'mode' => $mode,
            'tab' => $tab,
            'requestStatus' => $requestStatus,
            'daySchedules' => $daySchedules,
            'scheduleSlots' => $scheduleSlots,
            'tabSlots' => $tabSlots,
            'reservationSlots' => $reservationSlots,
            'availableSlots' => $availableSlots,
            'selectedSlot' => $selectedSlot,
            'selectedSlotDetails' => $selectedSlotDetails,
            'visiblePendingRequests' => $visiblePendingRequests,
            'visibleAcceptedRequests' => $visibleAcceptedRequests,
            'visibleOtherRequests' => $visibleOtherRequests,
            'requestHistory' => $requestHistory,
            'filters' => $filters + ['search' => $search, 'status' => 'all'],
            'slotCounts' => $slotCounts,
            'pendingRequests' => $pendingRequests,
            'acceptedRequests' => $acceptedRequests,
            'directReservations' => $directReservations,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\Exhibitor;
use App\Models\Slot;
use App\Models\SlotRequest;
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
        return view('responsable-technique.dashboard', [
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
            ],
        ]);
    }

    public function equipment(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100', Rule::exists('equipment', 'category')],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'all'])],
        ]);

        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? 'all';
        $equipment = Equipment::query()
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when(isset($filters['category']), fn ($query) => $query->where('category', $filters['category']))
            ->when($status !== 'all', fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(18)
            ->withQueryString();

        return view('responsable-technique.equipment', [
            'equipment' => $equipment,
            'categories' => Equipment::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'filters' => $filters + ['status' => $status, 'search' => $search],
        ]);
    }

    public function commercials(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'all'])],
        ]);
        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? 'all';

        $commercials = User::query()
            ->where('role', UserRole::Commercial)
            ->withCount('exhibitors')
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($status !== 'all', fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('responsable-technique.commercials', [
            'commercials' => $commercials,
            'filters' => $filters + ['status' => $status, 'search' => $search],
        ]);
    }

    public function commercial(User $commercial): View
    {
        abort_unless($commercial->role === UserRole::Commercial, 404);

        $exhibitors = $commercial->exhibitors()
            ->with('user:id,name,email,is_active')
            ->orderBy('company_name')
            ->paginate(20);

        return view('responsable-technique.commercial', [
            'commercial' => $commercial,
            'exhibitors' => $exhibitors,
        ]);
    }

    public function conferences(): View
    {
        $slots = Slot::query()
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

        $directReservations = $slots->filter(function (Slot $slot): bool {
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
            $status->value => $slots->where('status', $status)->count(),
        ]);

        return view('responsable-technique.conferences', [
            'slots' => $slots,
            'slotCounts' => $slotCounts,
            'pendingRequests' => $pendingRequests,
            'acceptedRequests' => $acceptedRequests,
            'directReservations' => $directReservations,
        ]);
    }
}

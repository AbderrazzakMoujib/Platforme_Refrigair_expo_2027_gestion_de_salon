<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Enums\SpaceType;
use App\Http\Controllers\Controller;
use App\Models\Slot;
use App\Models\SlotRequest;
use App\Services\SlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Consultation admin des Conférences & Lounge des Technologies :
 * qui a réservé quel créneau, statuts et chiffre d'affaires.
 * La gestion (attribution, statuts, demandes) reste au commercial.
 */
class SlotController extends Controller
{
    public function index(Request $request, SlotService $service): View
    {
        Gate::authorize('view', Slot::class);

        $filters = $request->validate([
            'space' => ['nullable', Rule::enum(SpaceType::class)],
            'status' => ['nullable', Rule::in(['taken', ...array_column(SlotStatus::cases(), 'value')])],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim($filters['search'] ?? '');

        $slots = Slot::query()
            ->with(['space:id,type,name', 'exhibitor:id,company_name,commercial_id', 'exhibitor.commercial:id,name'])
            ->when($filters['space'] ?? null, fn ($query, $space) => $query->whereHas('space', fn ($q) => $q->where('type', $space)))
            ->when($filters['status'] ?? null, function ($query, $status) {
                $status === 'taken'
                    ? $query->where('status', '!=', SlotStatus::Available->value)
                    : $query->where('status', $status);
            })
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('date', $date))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('exhibitor_name', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhereHas('exhibitor', fn ($e) => $e->where('company_name', 'like', "%{$search}%"));
                });
            })
            ->ordered()
            ->get();

        // Récapitulatif par titulaire (tous créneaux pris, indépendant des filtres).
        $holders = Slot::query()
            ->with(['space:id,type', 'exhibitor:id,company_name'])
            ->where('status', '!=', SlotStatus::Available->value)
            ->ordered()
            ->get()
            ->groupBy(fn (Slot $slot) => mb_strtolower((string) $slot->holder_name))
            ->map(fn ($group) => [
                'name' => $group->first()->holder_name,
                'in_database' => $group->first()->exhibitor_id !== null,
                'codes' => $group->pluck('code')->all(),
                'conference' => $group->filter(fn (Slot $s) => $s->space->type === SpaceType::Conference)->count(),
                'lounge' => $group->filter(fn (Slot $s) => $s->space->type === SpaceType::Lounge)->count(),
                'options' => $group->filter(fn (Slot $s) => $s->status === SlotStatus::Option)->count(),
                'revenue' => (float) $group->filter(fn (Slot $s) => $s->status->isSold())->sum(fn (Slot $s) => (float) $s->price_excl_tax),
                'collected' => (float) $group->filter(fn (Slot $s) => $s->status === SlotStatus::Paid)->sum(fn (Slot $s) => (float) $s->price_excl_tax),
            ])
            ->sortByDesc('revenue')
            ->values();

        return view('admin.slots.index', [
            'admin' => $request->user(),
            'adminInitials' => $request->user()->initials,
            'slots' => $slots,
            'holders' => $holders,
            'stats' => $service->stats(),
            'filters' => $filters + ['search' => $search],
            'statuses' => SlotStatus::cases(),
            'spaceTypes' => SpaceType::cases(),
            'days' => Slot::query()->select('date')->distinct()->orderBy('date')->pluck('date'),
            'pendingRequestsCount' => SlotRequest::query()->where('status', SlotRequestStatus::Pending)->count(),
        ]);
    }

    public function downloadSupport(Slot $slot): StreamedResponse
    {
        Gate::authorize('downloadSupport', $slot);

        abort_unless(Storage::disk('local')->exists($slot->support_path), 404);

        return Storage::disk('local')->download(
            $slot->support_path,
            $slot->code.'-'.($slot->support_original_name ?? basename($slot->support_path))
        );
    }
}

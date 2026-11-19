<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CommercialEquipmentRequestController extends Controller
{
    private const STATUSES = ['pending', 'approved', 'rejected'];

    public function index(Request $request): View
    {
        $commercial = $request->user();

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['all', ...self::STATUSES])],
            'search' => ['nullable', 'string', 'max:255'],
            'exhibitor_id' => ['nullable', 'integer'],
        ]);
        $status = $filters['status'] ?? 'all';
        $search = trim((string) ($filters['search'] ?? ''));
        $exhibitorId = $filters['exhibitor_id'] ?? null;

        $counts = $this->ownRequests($commercial)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count) => (int) $count);

        $requests = $this->ownRequests($commercial)
            ->with([
                'exhibitor:id,company_name,stand_number,logo',
                'items:id,equipment_request_id,equipment_name,quantity',
            ])
            ->withSum('items as total_quantity', 'quantity')
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($exhibitorId, fn ($query, $id) => $query->where('exhibitor_id', $id))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->whereHas('exhibitor', fn ($q) => $q->where(fn ($q) => $q
                        ->where('company_name', 'like', "%{$search}%")
                        ->orWhere('stand_number', 'like', "%{$search}%")))
                        ->orWhereHas('items', fn ($q) => $q->where('equipment_name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('commercial.requests.index', [
            ...$this->layoutData($commercial),
            'requests' => $requests,
            'status' => $status,
            'search' => $search,
            'exhibitorId' => $exhibitorId,
            'exhibitorOptions' => $commercial->exhibitors()->orderBy('company_name')->get(['id', 'company_name']),
            'pendingCount' => $counts->get('pending', 0),
            'approvedCount' => $counts->get('approved', 0),
            'rejectedCount' => $counts->get('rejected', 0),
            'totalCount' => $counts->sum(),
            'newThisMonth' => $this->ownRequests($commercial)->where('created_at', '>=', now()->startOfMonth())->count(),
            'recentReviews' => $this->ownRequests($commercial)
                ->whereNotNull('reviewed_at')
                ->with('exhibitor:id,company_name')
                ->latest('reviewed_at')
                ->limit(5)
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $commercial = $request->user();
        $exhibitors = $commercial->exhibitors()
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'email', 'phone', 'stand_number', 'area_sqm', 'logo']);

        $selectedId = (int) $request->query('exhibitor_id', old('exhibitor_id', 0));

        return view('commercial.requests.create', [
            ...$this->layoutData($commercial),
            'exhibitors' => $exhibitors,
            'selectedExhibitor' => $exhibitors->firstWhere('id', $selectedId),
            'equipment' => Equipment::query()
                ->where('is_active', true)
                ->orderBy('category')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $commercial = $request->user();

        $data = $request->validate([
            'exhibitor_id' => [
                'required',
                'integer',
                Rule::exists('exhibitors', 'id')->where('commercial_id', $commercial->id),
            ],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'exhibitor_id.exists' => 'Veuillez sélectionner un de vos exposants.',
        ]);

        $selected = collect($data['items'])->filter(fn ($quantity) => (int) $quantity > 0);

        if ($selected->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Veuillez sélectionner au moins un équipement.',
            ]);
        }

        // Mêmes règles que la demande envoyée par l'exposant (ExhibitorStandOptionsController@store).
        $order = DB::transaction(function () use ($selected, $data): EquipmentRequest {
            $items = [];
            $totalCents = 0;

            foreach ($selected as $id => $quantity) {
                if (! ctype_digit((string) $id) || (int) $id < 1) {
                    throw ValidationException::withMessages(['items' => 'Équipement invalide.']);
                }

                $equipment = Equipment::query()
                    ->whereKey((int) $id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (! $equipment) {
                    throw ValidationException::withMessages(['items' => 'Un équipement sélectionné est indisponible.']);
                }

                if ((int) $quantity > $equipment->quantity_available) {
                    throw ValidationException::withMessages(['items' => "Quantité indisponible : {$equipment->name}."]);
                }

                $priceCents = (int) round(((float) $equipment->price) * 100);
                $subtotalCents = $priceCents * (int) $quantity;
                $totalCents += $subtotalCents;

                $items[] = [
                    'equipment_id' => $equipment->id,
                    'equipment_name' => $equipment->name,
                    'quantity' => (int) $quantity,
                    'unit_price' => number_format($priceCents / 100, 2, '.', ''),
                    'subtotal' => number_format($subtotalCents / 100, 2, '.', ''),
                ];
            }

            $order = EquipmentRequest::create([
                'exhibitor_id' => (int) $data['exhibitor_id'],
                'status' => 'pending',
                'total_amount' => number_format($totalCents / 100, 2, '.', ''),
                'notes' => $data['notes'] ?? null,
            ]);

            $order->items()->createMany($items);

            return $order;
        });

        return redirect()
            ->route('commercial.requests.show', $order)
            ->with('status', 'Demande envoyée. Elle sera validée par l’administration.');
    }

    public function show(Request $request, EquipmentRequest $equipmentRequest): View
    {
        $commercial = $request->user();
        $equipmentRequest->load([
            'exhibitor',
            'items.equipment:id,name,category,image_path',
            'reviewer:id,name',
        ]);

        abort_unless(
            $equipmentRequest->exhibitor
                && (int) $equipmentRequest->exhibitor->commercial_id === (int) $commercial->id,
            404
        );

        return view('commercial.requests.show', [
            ...$this->layoutData($commercial),
            'order' => $equipmentRequest,
        ]);
    }

    /** Demandes des exposants rattachés au commercial connecté. */
    private function ownRequests(User $commercial): Builder
    {
        return EquipmentRequest::query()->whereHas(
            'exhibitor',
            fn ($query) => $query->where('commercial_id', $commercial->id)
        );
    }

    private function layoutData(User $commercial): array
    {
        return [
            'commercial' => $commercial,
            'commercialInitials' => collect(preg_split('/\s+/', trim($commercial->name)))
                ->filter()
                ->take(2)
                ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
                ->implode('') ?: 'C',
        ];
    }
}

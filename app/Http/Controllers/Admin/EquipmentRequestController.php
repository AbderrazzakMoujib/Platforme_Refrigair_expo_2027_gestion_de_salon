<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentRequest;
use App\Models\EquipmentRequestItem;
use App\Models\Exhibitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EquipmentRequestController extends Controller
{
    private function layoutData(Request $request): array
    {
        $admin = $request->user();

        $initials = collect(
            preg_split('/\s+/', trim($admin->name))
        )
            ->filter()
            ->take(2)
            ->map(
                fn (string $part) =>
                    mb_strtoupper(mb_substr($part, 0, 1))
            )
            ->implode('') ?: 'A';

        return [
            'admin' => $admin,
            'adminInitials' => $initials,
        ];
    }

    /**
     * Liste des demandes d'équipements.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'status' => [
                'nullable',
                'in:pending,approved,rejected',
            ],
            'exhibitor_id' => [
                'nullable',
                'integer',
                'exists:exhibitors,id',
            ],
            'date_from' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'date_to' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
            ],
        ]);

        $query = EquipmentRequest::query()
            ->with([
                'exhibitor:id,company_name,stand_number,commercial_id,logo',
                'exhibitor.commercial:id,name',
                'items:id,equipment_request_id,equipment_id,equipment_name,quantity',
                'items.equipment:id,image_path',
            ])
            ->withCount('items')
            ->withSum(
                'items as requested_quantity',
                'quantity'
            );

        // Recherche par exposant, stand, équipement ou ID.
        if (!empty($filters['search'])) {
            $term = trim($filters['search']);

            $query->where(function ($q) use ($term) {
                $q->whereHas(
                    'exhibitor',
                    function ($exhibitor) use ($term) {
                        $exhibitor
                            ->where(
                                'company_name',
                                'like',
                                "%{$term}%"
                            )
                            ->orWhere(
                                'stand_number',
                                'like',
                                "%{$term}%"
                            );
                    }
                )
                ->orWhereHas(
                    'items',
                    function ($item) use ($term) {
                        $item->where(
                            'equipment_name',
                            'like',
                            "%{$term}%"
                        );
                    }
                );

                if (ctype_digit($term)) {
                    $q->orWhere('id', (int) $term);
                }
            });
        }

        // Filtre statut.
        if (!empty($filters['status'])) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        // Filtre exposant.
        if (!empty($filters['exhibitor_id'])) {
            $query->where(
                'exhibitor_id',
                $filters['exhibitor_id']
            );
        }

        // Filtre date début.
        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'created_at',
                '>=',
                $filters['date_from']
            );
        }

        // Filtre date fin.
        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'created_at',
                '<=',
                $filters['date_to']
            );
        }

        $requests = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $exhibitors = Exhibitor::query()
            ->orderBy('company_name')
            ->get([
                'id',
                'company_name',
            ]);

        $totalRequests = EquipmentRequest::count();

        $pendingCount = EquipmentRequest::query()
            ->where('status', 'pending')
            ->count();

        $approvedCount = EquipmentRequest::query()
            ->where('status', 'approved')
            ->count();

        $rejectedCount = EquipmentRequest::query()
            ->where('status', 'rejected')
            ->count();

        $totalRequestedQuantity = (int)
            EquipmentRequestItem::query()
                ->sum('quantity');

        return view('admin.requests.index', [
            ...$this->layoutData($request),

            'requests' => $requests,
            'exhibitors' => $exhibitors,

            'totalRequests' => $totalRequests,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'totalRequestedQuantity' => $totalRequestedQuantity,
        ]);
    }

    /**
     * Détail d'une demande.
     */
    public function show(
        Request $request,
        EquipmentRequest $equipmentRequest
    ): View {
        $equipmentRequest->load([
            'exhibitor.commercial',
            'items.equipment',
            'reviewer:id,name',
        ]);

        return view('admin.requests.show', [
            ...$this->layoutData($request),
            'equipmentRequest' => $equipmentRequest,
        ]);
    }

    /**
     * Approuver une demande.
     * Vérifie le stock avant de valider.
     */
    public function approve(
        Request $request,
        EquipmentRequest $equipmentRequest
    ): RedirectResponse {
        DB::transaction(function () use (
            $equipmentRequest,
            $request
        ) {
            $order = EquipmentRequest::query()
                ->lockForUpdate()
                ->findOrFail($equipmentRequest->id);

            // Empêcher le traitement multiple.
            if ($order->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Cette demande a déjà été traitée.',
                ]);
            }

            $items = $order->items()
                ->orderBy('equipment_id')
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Cette demande ne contient aucun équipement.',
                ]);
            }

            foreach ($items as $item) {
                $equipment = Equipment::query()
                    ->whereKey($item->equipment_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Équipement désactivé.
                if (!$equipment->is_active) {
                    throw ValidationException::withMessages([
                        'status' =>
                            "Équipement indisponible : {$item->equipment_name}.",
                    ]);
                }

                // Stock insuffisant.
                if (
                    $equipment->quantity_available
                    < $item->quantity
                ) {
                    throw ValidationException::withMessages([
                        'status' =>
                            "Stock insuffisant : {$item->equipment_name}.",
                    ]);
                }

                // Déduire la quantité demandée.
                $equipment->decrement(
                    'quantity_available',
                    $item->quantity
                );
            }

            // Validation finale.
            $order->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);
        });

        return redirect()
            ->route(
                'admin.demandes.show',
                $equipmentRequest
            )
            ->with(
                'status',
                'Demande approuvée avec succès.'
            );
    }

    /**
     * Refuser une demande.
     * Le motif est obligatoire.
     */
    public function reject(
        Request $request,
        EquipmentRequest $equipmentRequest
    ): RedirectResponse {
        $data = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(function () use (
            $equipmentRequest,
            $request,
            $data
        ) {
            $order = EquipmentRequest::query()
                ->lockForUpdate()
                ->findOrFail($equipmentRequest->id);

            // Empêcher le traitement multiple.
            if ($order->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Cette demande a déjà été traitée.',
                ]);
            }

            $order->update([
                'status' => 'rejected',
                'rejection_reason' =>
                    $data['rejection_reason'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return redirect()
            ->route(
                'admin.demandes.show',
                $equipmentRequest
            )
            ->with(
                'status',
                'Demande refusée.'
            );
    }
}

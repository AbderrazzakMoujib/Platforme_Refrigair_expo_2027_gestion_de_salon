<?php

namespace App\Http\Controllers;

use App\Models\EquipmentRequest;
use App\Models\Salon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExhibitorRequestController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $exhibitor = $user
            ->exhibitorProfile()
            ->firstOrFail();

        $filters = $request->validate([
            'search' => [
                'nullable', 'string', 'max:255',
            ],
            'status' => [
                'nullable',
                Rule::in([
                    'all',
                    'pending',
                    'approved',
                    'rejected',
                ]),
            ],
            'selected' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? 'all';

        // Toutes les requêtes restent limitées
        // à l'entreprise connectée.
        $base = EquipmentRequest::query()
            ->where('exhibitor_id', $exhibitor->id);

        $totalCount = (clone $base)->count();

        $pendingCount = (clone $base)
            ->where('status', 'pending')
            ->count();

        $approvedCount = (clone $base)
            ->where('status', 'approved')
            ->count();

        $rejectedCount = (clone $base)
            ->where('status', 'rejected')
            ->count();

        $query = clone $base;

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                // Recherche par numéro de demande.
                if (ctype_digit($search)) {
                    $q->where('id', (int) $search);
                }

                // Recherche par équipement.
                $method = ctype_digit($search)
                    ? 'orWhereHas'
                    : 'whereHas';

                $q->{$method}(
                    'items',
                    fn ($items) => $items->where(
                        'equipment_name',
                        'like',
                        '%' . $search . '%'
                    )
                );
            });
        }

        $requests = $query
            ->withCount('items')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $selectedRequest = null;

        if (!empty($filters['selected'])) {
            $selectedRequest = (clone $base)
                ->with([
                    'items.equipment:id,name,image_path',
                ])
                ->find($filters['selected']);
        }

        if (!$selectedRequest && $requests->isNotEmpty()) {
            $selectedRequest = (clone $base)
                ->with([
                    'items.equipment:id,name,image_path',
                ])
                ->find($requests->first()->id);
        }

        $salon = Salon::query()
            ->whereDate('end_date', '>=', today())
            ->whereNotIn('status', [
                'completed',
                'cancelled',
            ])
            ->orderBy('start_date')
            ->first();

        return view('exhibitor.requests.index', [
            'user' => $user,
            'exhibitor' => $exhibitor,
            'requests' => $requests,
            'selectedRequest' => $selectedRequest,
            'salon' => $salon,
            'search' => $search,
            'status' => $status,
            'totalCount' => $totalCount,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
        ]);
    }
}

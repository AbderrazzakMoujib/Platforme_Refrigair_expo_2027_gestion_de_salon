<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentRequest;
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

        return [
            'admin' => $admin,
            'adminInitials' => collect(
                preg_split('/\s+/', trim($admin->name))
            )
                ->filter()
                ->take(2)
                ->map(fn (string $part) =>
                    mb_strtoupper(mb_substr($part, 0, 1))
                )
                ->implode('') ?: 'A',
        ];
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => [
                'nullable',
                'in:pending,approved,rejected',
            ],
        ]);

        $query = EquipmentRequest::query()
            ->with([
                'exhibitor:id,company_name,stand_number,commercial_id',
                'exhibitor.commercial:id,name',
            ])
            ->withCount('items');

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);

            $query->where(function ($q) use ($term) {
                $q->whereHas('exhibitor', function ($q) use ($term) {
                    $q->where('company_name', 'like', "%{$term}%")
                        ->orWhere('stand_number', 'like', "%{$term}%");
                });

                if (ctype_digit($term)) {
                    $q->orWhere('id', (int) $term);
                }
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return view('admin.requests.index', [
            ...$this->layoutData($request),
            'requests' => $query
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'totalRequests' => EquipmentRequest::count(),
            'pendingCount' => EquipmentRequest::where(
                'status', 'pending'
            )->count(),
            'approvedCount' => EquipmentRequest::where(
                'status', 'approved'
            )->count(),
            'rejectedCount' => EquipmentRequest::where(
                'status', 'rejected'
            )->count(),
        ]);
    }

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

    public function approve(
        Request $request,
        EquipmentRequest $equipmentRequest
    ): RedirectResponse {
        DB::transaction(function () use ($equipmentRequest, $request) {
            $order = EquipmentRequest::query()
                ->lockForUpdate()
                ->findOrFail($equipmentRequest->id);

            if ($order->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Cette demande a déjà été traitée.',
                ]);
            }

            $items = $order->items()->orderBy('equipment_id')->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'status' => 'Cette demande ne contient aucun équipement.',
                ]);
            }

            foreach ($items as $item) {
                $equipment = Equipment::query()
                    ->whereKey($item->equipment_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $equipment->is_active) {
                    throw ValidationException::withMessages([
                        'status' => "Équipement indisponible : {$item->equipment_name}.",
                    ]);
                }

                if ($equipment->quantity_available < $item->quantity) {
                    throw ValidationException::withMessages([
                        'status' => "Stock insuffisant : {$item->equipment_name}.",
                    ]);
                }

                $equipment->decrement(
                    'quantity_available',
                    $item->quantity
                );
            }

            $order->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);
        });

        return redirect()
            ->route('admin.demandes.show', $equipmentRequest)
            ->with('status', 'Demande approuvée avec succès.');
    }

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

            if ($order->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Cette demande a déjà été traitée.',
                ]);
            }

            $order->update([
                'status' => 'rejected',
                'rejection_reason' => $data['rejection_reason'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.demandes.show', $equipmentRequest)
            ->with('status', 'Demande refusée.');
    }
}

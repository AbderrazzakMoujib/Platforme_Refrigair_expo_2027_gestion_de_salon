<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExhibitorStandOptionsController extends Controller
{
    private function exhibitor(Request $request)
    {
        return $request->user()
            ->exhibitorProfile()
            ->firstOrFail();
    }

    public function index(Request $request): View
    {
        $exhibitor = $this->exhibitor($request);

        return view('exhibitor.stand-options.index', [
            'user' => $request->user(),
            'exhibitor' => $exhibitor,
            'equipment' => Equipment::query()
                ->where('is_active', true)
                ->orderBy('category')
                ->orderBy('name')
                ->get(),
            'requests' => EquipmentRequest::query()
                ->where('exhibitor_id', $exhibitor->id)
                ->with('items')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $exhibitor = $this->exhibitor($request);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*' => ['integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $selected = collect($data['items'])
            ->filter(fn ($quantity) => (int) $quantity > 0);

        if ($selected->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Veuillez sélectionner au moins un équipement.',
            ]);
        }

        DB::transaction(function () use ($selected, $data, $exhibitor) {
            $items = [];
            $totalCents = 0;

            foreach ($selected as $id => $quantity) {
                if (!ctype_digit((string) $id) || (int) $id < 1) {
                    throw ValidationException::withMessages([
                        'items' => 'Équipement invalide.',
                    ]);
                }

                $equipment = Equipment::query()
                    ->whereKey((int) $id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (!$equipment) {
                    throw ValidationException::withMessages([
                        'items' => 'Un équipement sélectionné est indisponible.',
                    ]);
                }

                if ((int) $quantity > $equipment->quantity_available) {
                    throw ValidationException::withMessages([
                        'items' => "Quantité indisponible : {$equipment->name}.",
                    ]);
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
                'exhibitor_id' => $exhibitor->id,
                'status' => 'pending',
                'total_amount' => number_format($totalCents / 100, 2, '.', ''),
                'notes' => $data['notes'] ?? null,
            ]);

            $order->items()->createMany($items);
        });

        
        return redirect()
            ->route('exhibitor.requests.index')
             ->with(
             'status',
             'Votre demande a été envoyée avec succès.'
            );

    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommercialEquipmentController extends Controller
{
    private const CATEGORIES = [
        'Mobilier',
        'Électricité',
        'Audiovisuel',
        'Informatique',
        'Restauration',
        'Nettoyage',
        'Autres',
    ];

    private function layoutData(Request $request): array
    {
        return [
            'commercial' => $request->user(),
            'commercialInitials' => $request->user()->initials,
        ];
    }

    private function equipmentQuery(Request $request)
    {
        return Equipment::query()
            ->where('commercial_id', $request->user()->id);
    }

    private function authorizeEquipment(
        Request $request,
        Equipment $equipment
    ): void {
        abort_unless(
            (int) $equipment->commercial_id === (int) $request->user()->id,
            403
        );
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => [
                'nullable',
                Rule::in(self::CATEGORIES),
            ],
            'status' => [
                'nullable',
                Rule::in(['active', 'inactive']),
            ],
        ]);

        $query = $this->equipmentQuery($request);

        $totalEquipment = (clone $query)->count();

        $activeEquipment = (clone $query)
            ->where('is_active', true)
            ->count();

        $inactiveEquipment = (clone $query)
            ->where('is_active', false)
            ->count();

        $totalQuantity = (clone $query)
            ->where('is_active', true)
            ->sum('quantity_available');

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);

            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $query->where(
                'is_active',
                $filters['status'] === 'active'
            );
        }

        return view('commercial.equipment.index', [
            ...$this->layoutData($request),

            'equipment' => $query
                ->latest()
                ->paginate(10)
                ->withQueryString(),

            'categories' => self::CATEGORIES,
            'totalEquipment' => $totalEquipment,
            'activeEquipment' => $activeEquipment,
            'inactiveEquipment' => $inactiveEquipment,
            'totalQuantity' => $totalQuantity,
        ]);
    }

    public function create(Request $request): View
    {
        return view('commercial.equipment.form', [
            ...$this->layoutData($request),

            'equipmentItem' => new Equipment([
                'is_active' => true,
                'quantity_available' => 0,
                'price' => 0,
            ]),

            'categories' => self::CATEGORIES,
            'pageTitle' => 'Ajouter un équipement',
            'formAction' => route('commercial.equipements.store'),
            'formMethod' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $image = $data['image'] ?? null;

        unset($data['image']);

        $data['commercial_id'] = $request->user()->id;

        if ($image) {
            $data['image_path'] = $image->store(
                'equipment',
                'public'
            );
        }

        try {
            Equipment::create($data);

        } catch (\Throwable $exception) {

            if (!empty($data['image_path'])) {
                Storage::disk('public')
                    ->delete($data['image_path']);
            }

            throw $exception;
        }

        return redirect()
            ->route('commercial.equipements.index')
            ->with('status', 'Équipement ajouté avec succès.');
    }

    public function edit(
        Request $request,
        Equipment $equipment
    ): View {

        $this->authorizeEquipment($request, $equipment);

        return view('commercial.equipment.form', [
            ...$this->layoutData($request),

            'equipmentItem' => $equipment,
            'categories' => self::CATEGORIES,
            'pageTitle' => 'Modifier un équipement',

            'formAction' => route(
                'commercial.equipements.update',
                $equipment
            ),

            'formMethod' => 'PUT',
        ]);
    }

    public function update(
        Request $request,
        Equipment $equipment
    ): RedirectResponse {

        $this->authorizeEquipment($request, $equipment);

        $data = $this->validated($request);

        $image = $data['image'] ?? null;

        unset($data['image']);

        $oldImage = $equipment->image_path;
        $newImage = null;

        if ($image) {
            $newImage = $image->store(
                'equipment',
                'public'
            );

            $data['image_path'] = $newImage;
        }

        try {
            $equipment->update($data);

        } catch (\Throwable $exception) {

            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        if ($newImage && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()
            ->route('commercial.equipements.index')
            ->with('status', 'Équipement modifié avec succès.');
    }

    public function toggle(
        Request $request,
        Equipment $equipment
    ): RedirectResponse {

        $this->authorizeEquipment($request, $equipment);

        $equipment->update([
            'is_active' => !$equipment->is_active,
        ]);

        return back()->with(
            'status',
            $equipment->is_active
                ? 'Équipement activé.'
                : 'Équipement désactivé.'
        );
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'category' => [
                'required',
                Rule::in(self::CATEGORIES),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:99999999.99',
                'decimal:0,2',
            ],

            'quantity_available' => [
                'required',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);
    }
}

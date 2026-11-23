<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    /**
     * Catalogue partagé entre Admin et tous les Commerciaux.
     *
     * commercial_id sert uniquement à identifier
     * le créateur de l'équipement.
     */
    private function equipmentQuery()
    {
        return Equipment::query();
    }

    /**
     * Liste des équipements du catalogue commun.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'category' => [
                'nullable',
                Rule::in(self::CATEGORIES),
            ],
            'status' => [
                'nullable',
                Rule::in(['active', 'inactive']),
            ],
        ]);

        $baseQuery = $this->equipmentQuery();

        $totalEquipment = (clone $baseQuery)->count();

        $activeEquipment = (clone $baseQuery)
            ->where('is_active', true)
            ->count();

        $inactiveEquipment = (clone $baseQuery)
            ->where('is_active', false)
            ->count();

        $totalQuantity = (int) (clone $baseQuery)
            ->where('is_active', true)
            ->sum('quantity_available');

        $query = $this->equipmentQuery()
            ->with('commercial:id,name');

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);

            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas(
                        'commercial',
                        fn ($commercial) => $commercial
                            ->where('name', 'like', "%{$term}%")
                    );
            });
        }

        if (!empty($filters['category'])) {
            $query->where(
                'category',
                $filters['category']
            );
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

    /**
     * Formulaire de création.
     */
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

    /**
     * Ajouter un équipement dans le catalogue commun.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $image = $data['image'] ?? null;

        unset($data['image']);

        // On conserve l'identité du créateur.
        // Mais l'équipement reste visible par tous.
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
            ->with(
                'status',
                'Équipement ajouté au catalogue commun avec succès.'
            );
    }

    /**
     * Modifier n'importe quel équipement
     * du catalogue commun.
     *
     * L'accès reste limité au rôle Commercial
     * grâce aux middlewares des routes.
     */
    public function edit(
        Request $request,
        Equipment $equipment
    ): View {
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

    /**
     * Enregistrer les modifications
     * d'un équipement partagé.
     */
    public function update(
        Request $request,
        Equipment $equipment
    ): RedirectResponse {
        $data = $this->validated($request);

        $image = $data['image'] ?? null;

        unset($data['image']);

        // commercial_id n'est jamais modifié ici.
        // Le créateur original reste enregistré.

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
                Storage::disk('public')
                    ->delete($newImage);
            }

            throw $exception;
        }

        if ($newImage && $oldImage) {
            Storage::disk('public')
                ->delete($oldImage);
        }

        return redirect()
            ->route('commercial.equipements.index')
            ->with(
                'status',
                'Équipement du catalogue mis à jour avec succès.'
            );
    }

    /**
     * Activer ou désactiver un équipement partagé.
     */
    public function toggle(
        Request $request,
        Equipment $equipment
    ): RedirectResponse {
        $equipment->update([
            'is_active' => ! $equipment->is_active,
        ]);

        return back()->with(
            'status',
            $equipment->is_active
                ? 'Équipement activé dans le catalogue.'
                : 'Équipement désactivé dans le catalogue.'
        );
    }

    /**
     * Validation commune Create / Update.
     */
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

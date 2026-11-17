<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EquipmentController extends Controller
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
            'admin' => $request->user(),
            'adminInitials' => $request->user()->initials,
        ];
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
            'commercial_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
        ]);

        $equipment = Equipment::query()
            ->with([
                'commercial:id,name,email,profile_photo',
            ])
            ->when(
                !empty($filters['search']),
                function ($query) use ($filters) {
                    $term = trim($filters['search']);

                    $query->where(function ($q) use ($term) {
                        $q->where(
                            'name',
                            'like',
                            "%{$term}%"
                        )
                        ->orWhere(
                            'description',
                            'like',
                            "%{$term}%"
                        )
                        ->orWhereHas(
                            'commercial',
                            fn ($c) => $c->where(
                                'name',
                                'like',
                                "%{$term}%"
                            )
                        );
                    });
                }
            )
            ->when(
                !empty($filters['category']),
                fn ($q) => $q->where(
                    'category',
                    $filters['category']
                )
            )
            ->when(
                !empty($filters['status']),
                fn ($q) => $q->where(
                    'is_active',
                    $filters['status'] === 'active'
                )
            )
            ->when(
                !empty($filters['commercial_id']),
                fn ($q) => $q->where(
                    'commercial_id',
                    $filters['commercial_id']
                )
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalEquipment = Equipment::count();

        $activeEquipment = Equipment::query()
            ->where('is_active', true)
            ->count();

        $inactiveEquipment = Equipment::query()
            ->where('is_active', false)
            ->count();

        $totalQuantity = (int) Equipment::query()
            ->where('is_active', true)
            ->sum('quantity_available');

        $commercials = User::query()
            ->where('role', 'commercial')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.equipment.index', [
            ...$this->layoutData($request),
            'equipment' => $equipment,
            'commercials' => $commercials,
            'categories' => self::CATEGORIES,
            'totalEquipment' => $totalEquipment,
            'activeEquipment' => $activeEquipment,
            'inactiveEquipment' => $inactiveEquipment,
            'totalQuantity' => $totalQuantity,
        ]);
    }

    // Conservé pour compatibilité des anciennes routes.
    public function create(Request $request): View
    {
        return view('admin.equipment.form', [
            ...$this->layoutData($request),
            'equipmentItem' => new Equipment([
                'is_active' => true,
                'quantity_available' => 0,
                'price' => 0,
            ]),
            'categories' => self::CATEGORIES,
            'pageTitle' => 'Ajouter un équipement',
            'formAction' => route('admin.equipements.store'),
            'formMethod' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $image = $data['image'] ?? null;
        unset($data['image']);

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
            ->route('admin.equipements.index')
            ->with('status', 'Équipement ajouté avec succès.');
    }

    public function edit(
        Request $request,
        Equipment $equipment
    ): View {
        return view('admin.equipment.form', [
            ...$this->layoutData($request),
            'equipmentItem' => $equipment,
            'categories' => self::CATEGORIES,
            'pageTitle' => 'Modifier un équipement',
            'formAction' => route(
                'admin.equipements.update',
                $equipment
            ),
            'formMethod' => 'PUT',
        ]);
    }

    public function update(
        Request $request,
        Equipment $equipment
    ): RedirectResponse {
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
            ->route('admin.equipements.index')
            ->with('status', 'Équipement modifié avec succès.');
    }

    public function toggle(
        Equipment $equipment
    ): RedirectResponse {
        $equipment->update([
            'is_active' => ! $equipment->is_active,
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

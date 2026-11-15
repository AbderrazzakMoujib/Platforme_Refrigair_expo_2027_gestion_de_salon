<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
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
        $admin = $request->user();

        return [
            'admin' => $admin,
            'adminInitials' => collect(
                preg_split('/\s+/', trim($admin->name))
            )
                ->filter()
                ->take(2)
                ->map(fn ($part) =>
                    mb_strtoupper(mb_substr($part, 0, 1))
                )
                ->implode('') ?: 'A',
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
        ]);

        $equipment = Equipment::query()
            ->when(
                !empty($filters['search']),
                function ($query) use ($filters) {
                    $term = trim($filters['search']);

                    $query->where(function ($query) use ($term) {
                        $query->where('name', 'like', "%{$term}%")
                            ->orWhere('description', 'like', "%{$term}%");
                    });
                }
            )
            ->when(
                !empty($filters['category']),
                fn ($query) =>
                    $query->where('category', $filters['category'])
            )
            ->when(
                !empty($filters['status']),
                fn ($query) =>
                    $query->where(
                        'is_active',
                        $filters['status'] === 'active'
                    )
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.equipment.index', [
            ...$this->layoutData($request),
            'equipment' => $equipment,
            'categories' => self::CATEGORIES,
            'totalEquipment' => Equipment::count(),
            'activeEquipment' => Equipment::where('is_active', true)->count(),
            'inactiveEquipment' => Equipment::where('is_active', false)->count(),
        ]);
    }

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
                Storage::disk('public')->delete($data['image_path']);
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
            'formAction' => route('admin.equipements.update', $equipment),
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
            $newImage = $image->store('equipment', 'public');
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
            'name' => ['required', 'string', 'max:255'],
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

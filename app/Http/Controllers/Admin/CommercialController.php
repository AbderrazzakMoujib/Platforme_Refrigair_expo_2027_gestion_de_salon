<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommercialController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $base = User::query()
            ->where('role', UserRole::Commercial->value);

        $totalCommercials = (clone $base)->count();

        $activeCommercials = (clone $base)
            ->where('is_active', true)
            ->count();

        $inactiveCommercials = (clone $base)
            ->where('is_active', false)
            ->count();

        $totalExhibitors = (clone $base)
            ->withCount('exhibitors')
            ->get()
            ->sum('exhibitors_count');

        $commercials = User::query()
            ->where('role', UserRole::Commercial->value)
            ->withCount('exhibitors')
            ->when(
                !empty($filters['search']),
                function ($query) use ($filters) {
                    $search = trim($filters['search']);

                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                !empty($filters['status']),
                fn ($query) => $query->where(
                    'is_active',
                    $filters['status'] === 'active'
                )
            )
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.commerciaux.index', [
            'admin' => $request->user(),
            'adminInitials' => $request->user()->initials,
            'commercials' => $commercials,
            'totalCommercials' => $totalCommercials,
            'activeCommercials' => $activeCommercials,
            'inactiveCommercials' => $inactiveCommercials,
            'totalExhibitors' => $totalExhibitors,
        ]);
    }

    public function create(Request $request): View
    {
        return $this->formView(
            $request,
            new User(['is_active' => true]),
            'Créer un commercial',
            'admin.commerciaux.store'
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
            ],
            'is_active' => ['required', 'boolean'],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        unset($validated['profile_photo']);

        $photoPath = null;

        if ($request->hasFile('profile_photo')) {
            $photoPath = $request
                ->file('profile_photo')
                ->store('commerciaux', 'public');
        }

        try {
            $commercial = User::query()->create([
                ...$validated,
                'profile_photo' => $photoPath,
                'role' => UserRole::Commercial,
                'is_active' => (bool) $validated['is_active'],
            ]);
        } catch (\Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.commerciaux.show', $commercial)
            ->with('status', 'Commercial créé avec succès.');
    }

    public function show(Request $request, User $commercial): View
    {
        $this->ensureCommercial($commercial);

        $commercial->loadCount('exhibitors');

        return view('admin.commerciaux.show', [
            'admin' => $request->user(),
            'adminInitials' => $request->user()->initials,
            'commercial' => $commercial,
            'accessStatus' => $commercial->password === null
                ? 'Mot de passe à définir'
                : 'Accès activé',
        ]);
    }

    public function edit(Request $request, User $commercial): View
    {
        $this->ensureCommercial($commercial);

        return $this->formView(
            $request,
            $commercial,
            'Modifier un commercial',
            'admin.commerciaux.update',
            'put'
        );
    }

    public function update(
        Request $request,
        User $commercial
    ): RedirectResponse {
        $this->ensureCommercial($commercial);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($commercial->id),
            ],
            'phone' => ['nullable', 'string', 'max:40'],
            'is_active' => ['required', 'boolean'],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'remove_profile_photo' => [
                'nullable',
                'boolean',
            ],
        ]);

        unset(
            $validated['profile_photo'],
            $validated['remove_profile_photo']
        );

        $previousPhoto = $commercial->profile_photo;
        $newPhoto = null;

        if ($request->hasFile('profile_photo')) {
            $newPhoto = $request
                ->file('profile_photo')
                ->store('commerciaux', 'public');

            $validated['profile_photo'] = $newPhoto;
        } elseif ($request->boolean('remove_profile_photo')) {
            $validated['profile_photo'] = null;
        }

        try {
            $commercial->update([
                ...$validated,
                'is_active' => (bool) $validated['is_active'],
            ]);
        } catch (\Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if (
            $previousPhoto
            && $previousPhoto !== $commercial->profile_photo
        ) {
            Storage::disk('public')->delete($previousPhoto);
        }

        return redirect()
            ->route('admin.commerciaux.show', $commercial)
            ->with('status', 'Informations du commercial mises à jour.');
    }

    private function ensureCommercial(User $user): void
    {
        abort_unless(
            $user->role === UserRole::Commercial,
            404
        );
    }

    private function formView(
        Request $request,
        User $commercial,
        string $title,
        string $route,
        ?string $method = null
    ): View {
        return view('admin.commerciaux.form', [
            'admin' => $request->user(),
            'adminInitials' => $request->user()->initials,
            'commercial' => $commercial,
            'pageTitle' => $title,
            'formRoute' => $route,
            'formMethod' => $method,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ProfilePhotoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ConferenceManagerController extends Controller
{
    public function index(Request $request): View
    {
        $managers = User::query()
            ->where('role', UserRole::ResponsableConferences->value)
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.responsables-conferences.index', [
            'admin' => $request->user(),
            'adminInitials' => $request->user()->initials,
            'managers' => $managers,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.responsables-conferences.form', [
            'admin' => $request->user(),
            'adminInitials' => $request->user()->initials,
            'manager' => new User(['is_active' => true]),
            'editing' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($data['profile_photo']);
        $photoPath = $request->hasFile('profile_photo')
            ? ProfilePhotoStorage::store($request->file('profile_photo'))
            : null;

        try {
            $manager = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::ResponsableConferences,
                'is_active' => true,
                'profile_photo' => $photoPath,
            ]);
        } catch (\Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }

        return redirect()->route('admin.responsables-conferences.index')
            ->with('status', "Le compte de {$manager->name} a été créé.");
    }

    public function edit(Request $request, User $manager): View
    {
        $this->ensureManager($manager);

        return view('admin.responsables-conferences.form', [
            'admin' => $request->user(),
            'adminInitials' => $request->user()->initials,
            'manager' => $manager,
            'editing' => true,
        ]);
    }

    public function update(Request $request, User $manager): RedirectResponse
    {
        $this->ensureManager($manager);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($manager->id)],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_profile_photo' => ['nullable', 'boolean'],
        ]);

        unset($data['profile_photo'], $data['remove_profile_photo']);
        $previousPhoto = $manager->profile_photo;
        $newPhoto = $request->hasFile('profile_photo')
            ? ProfilePhotoStorage::store($request->file('profile_photo'))
            : null;

        if ($newPhoto) {
            $data['profile_photo'] = $newPhoto;
        } elseif ($request->boolean('remove_profile_photo')) {
            $data['profile_photo'] = null;
        }

        try {
            if (! $manager->update($data)) {
                throw new \RuntimeException('Le compte responsable n’a pas pu être mis à jour.');
            }
        } catch (\Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($previousPhoto !== $manager->profile_photo) {
            ProfilePhotoStorage::deleteIfUnreferenced($previousPhoto, $manager);
        }

        return redirect()->route('admin.responsables-conferences.index')
            ->with('status', 'Le compte a été mis à jour.');
    }

    public function resetPassword(Request $request, User $manager): RedirectResponse
    {
        $this->ensureManager($manager);

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $manager->forceFill(['password' => Hash::make($data['password'])])->save();

        return back()->with('status', 'Le mot de passe a été réinitialisé.');
    }

    public function toggleAccess(Request $request, User $manager): RedirectResponse
    {
        $this->ensureManager($manager);

        $manager->forceFill(['is_active' => ! $manager->is_active])->save();

        return back()->with('status', $manager->is_active
            ? 'Accès réactivé.'
            : 'Accès révoqué. Le compte est désactivé.');
    }

    private function ensureManager(User $manager): void
    {
        abort_unless($manager->role === UserRole::ResponsableConferences, 404);
    }
}

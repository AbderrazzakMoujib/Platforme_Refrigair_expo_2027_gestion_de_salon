<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountInvitationService;
use App\Support\ProfilePhotoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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
            ->with('latestInvitation')
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

    public function store(Request $request, AccountInvitationService $invitations): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($data['profile_photo']);
        $photoPath = $request->hasFile('profile_photo')
            ? ProfilePhotoStorage::store($request->file('profile_photo'))
            : null;

        try {
            [$manager, $issued] = DB::transaction(function () use ($data, $photoPath, $invitations) {
                $manager = User::query()->create([
                    'name' => $data['name'], 'email' => $data['email'], 'password' => null,
                    'role' => UserRole::ResponsableConferences, 'is_active' => true,
                    'profile_photo' => $photoPath,
                ]);
                return [$manager, $invitations->issueFor($manager)];
            });
        } catch (\Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }

        $sent = $invitations->deliver($issued['invitation'], $issued['token']);
        return redirect()->route('admin.responsables-conferences.index')
            ->with($sent ? 'status' : 'warning', $sent
                ? "Le compte de {$manager->name} a été créé et l’invitation envoyée."
                : "Le compte de {$manager->name} est créé, mais le courriel n’a pas été envoyé.");
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

        $emailChanged = $data['email'] !== $manager->email;

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
            if ($emailChanged && $manager->password === null) {
                app(AccountInvitationService::class)->revokeOutstanding($manager);
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
        abort_if($manager->password === null, 409, 'Le responsable doit d’abord accepter son invitation.');

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
        if (! $manager->is_active) {
            app(AccountInvitationService::class)->revokeOutstanding($manager);
        }

        return back()->with('status', $manager->is_active
            ? 'Accès réactivé.'
            : 'Accès révoqué. Le compte est désactivé.');
    }

    public function resendInvitation(User $manager, AccountInvitationService $invitations): RedirectResponse
    {
        $this->ensureManager($manager);
        $issued = $invitations->prepareResend($manager);
        if (! isset($issued['invitation'])) {
            return back()->with('warning', match ($issued['reason'] ?? '') {
                'activated' => 'Ce compte est déjà activé.',
                'disabled' => 'Réactivez le compte avant de renvoyer une invitation.',
                'recent' => 'Une invitation vient d’être envoyée. Réessayez dans une minute.',
                default => 'Cette invitation ne peut pas être renvoyée.',
            });
        }
        $sent = $invitations->deliver($issued['invitation'], $issued['token']);
        return back()->with($sent ? 'status' : 'warning', $sent
            ? 'Invitation envoyée.'
            : 'Le courriel n’a pas pu être envoyé. Vérifiez le SMTP puis réessayez.');
    }

    private function ensureManager(User $manager): void
    {
        abort_unless($manager->role === UserRole::ResponsableConferences, 404);
    }
}

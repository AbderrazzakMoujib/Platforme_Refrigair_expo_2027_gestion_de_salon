<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialController extends Controller
{
    public function index(Request $request): View
    {
        $commercials = User::query()
            ->where('role', UserRole::Commercial->value)
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($request->input('status'), ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $request->input('status') === 'active'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.commerciaux.index', [
            'admin' => $request->user(),
            'adminInitials' => $this->initials($request->user()->name),
            'commercials' => $commercials,
        ]);
    }

    public function create(Request $request): View
    {
        return $this->formView($request, new User(['is_active' => true]), 'Créer un commercial', 'admin.commerciaux.store');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'is_active' => ['required', 'boolean'],
        ]);

        $commercial = User::query()->create([
            ...$validated,
            'role' => UserRole::Commercial,
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()->route('admin.commerciaux.show', $commercial)->with('status', 'Commercial créé.');
    }

    public function show(Request $request, User $commercial): View
    {
        $this->ensureCommercial($commercial);

        return view('admin.commerciaux.show', [
            'admin' => $request->user(),
            'adminInitials' => $this->initials($request->user()->name),
            'commercial' => $commercial,
            'accessStatus' => $commercial->password === null ? 'Mot de passe à définir' : 'Accès activé',
        ]);
    }

    public function edit(Request $request, User $commercial): View
    {
        $this->ensureCommercial($commercial);

        return $this->formView($request, $commercial, 'Modifier un commercial', 'admin.commerciaux.update', 'put');
    }

    public function update(Request $request, User $commercial): RedirectResponse
    {
        $this->ensureCommercial($commercial);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$commercial->id],
            'phone' => ['nullable', 'string', 'max:40'],
            'is_active' => ['required', 'boolean'],
        ]);

        $commercial->update([
            ...$validated,
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()->route('admin.commerciaux.show', $commercial)->with('status', 'Informations du commercial mises à jour.');
    }

    private function ensureCommercial(User $user): void
    {
        abort_unless($user->role === UserRole::Commercial, 404);
    }

    private function formView(Request $request, User $commercial, string $title, string $route, ?string $method = null): View
    {
        return view('admin.commerciaux.form', [
            'admin' => $request->user(),
            'adminInitials' => $this->initials($request->user()->name),
            'commercial' => $commercial,
            'pageTitle' => $title,
            'formRoute' => $route,
            'formMethod' => $method,
        ]);
    }

    private function initials(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)))
            ->filter()->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'A';
    }
}

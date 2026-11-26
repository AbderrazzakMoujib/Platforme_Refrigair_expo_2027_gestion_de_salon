<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TechnicalManagerController extends Controller
{
    public function index(Request $request): View
    {
        $technicians = User::query()
            ->where('role', UserRole::ResponsableTechnique->value)
            ->with('latestInvitation')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.responsables-techniques.index', [
            'admin' => $request->user(),
            'adminInitials' => $request->user()->initials,
            'technicians' => $technicians,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.responsables-techniques.form', [
            'admin' => $request->user(),
            'adminInitials' => $request->user()->initials,
            'technician' => new User(['is_active' => true]),
            'editing' => false,
        ]);
    }

    public function store(Request $request, AccountInvitationService $invitations): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        ]);

        [$technician, $issued] = DB::transaction(function () use ($data, $invitations): array {
            $technician = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => null,
                'role' => UserRole::ResponsableTechnique,
                'is_active' => true,
            ]);

            return [$technician, $invitations->issueFor($technician)];
        });

        $sent = $invitations->deliver($issued['invitation'], $issued['token']);

        return redirect()->route('admin.responsables-techniques.index')
            ->with($sent ? 'status' : 'warning', $sent
                ? "Le compte de {$technician->name} a été créé et l’invitation envoyée."
                : "Le compte de {$technician->name} est créé, mais le courriel n’a pas été envoyé.");
    }

    public function edit(Request $request, User $technician): View
    {
        $this->ensureTechnician($technician);

        return view('admin.responsables-techniques.form', [
            'admin' => $request->user(),
            'adminInitials' => $request->user()->initials,
            'technician' => $technician,
            'editing' => true,
        ]);
    }

    public function update(Request $request, User $technician, AccountInvitationService $invitations): RedirectResponse
    {
        $this->ensureTechnician($technician);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($technician->id)],
        ]);

        $emailChanged = $data['email'] !== $technician->email;
        $technician->update($data);

        if ($emailChanged && $technician->password === null) {
            $invitations->revokeOutstanding($technician);
        }

        return redirect()->route('admin.responsables-techniques.index')->with('status', 'Le compte a été mis à jour.');
    }

    public function toggleAccess(User $technician, AccountInvitationService $invitations): RedirectResponse
    {
        $this->ensureTechnician($technician);
        $technician->forceFill(['is_active' => ! $technician->is_active])->save();

        if (! $technician->is_active) {
            $invitations->revokeOutstanding($technician);
        }

        return back()->with('status', $technician->is_active
            ? 'Accès réactivé.'
            : 'Accès révoqué. Le compte est désactivé.');
    }

    public function resendInvitation(User $technician, AccountInvitationService $invitations): RedirectResponse
    {
        $this->ensureTechnician($technician);
        $issued = $invitations->prepareResend($technician);

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

    public function resetPassword(Request $request, User $technician): RedirectResponse
    {
        $this->ensureTechnician($technician);
        abort_if($technician->password === null, 409, 'Le Responsable Technique doit d’abord accepter son invitation.');

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $technician->forceFill(['password' => Hash::make($data['password'])])->save();

        return back()->with('status', 'Le mot de passe a été réinitialisé.');
    }

    private function ensureTechnician(User $technician): void
    {
        abort_unless($technician->role === UserRole::ResponsableTechnique, 404);
    }
}

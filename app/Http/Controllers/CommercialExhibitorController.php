<?php

namespace App\Http\Controllers;

use App\Models\Exhibitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;

class CommercialExhibitorController extends Controller
{
    public function index(Request $request): View
    {
        $commercial = $request->user();
        $exhibitors = $commercial->exhibitors()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = trim((string) $request->input('search'));
                $query->where(function ($query) use ($term): void {
                    $query->where('company_name', 'like', "%{$term}%")
                        ->orWhere('contact_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('commercial.exhibitors.index', [
            ...$this->layoutData($commercial),
            'exhibitors' => $exhibitors,
        ]);
    }

    public function create(Request $request): View
    {
        return view('commercial.exhibitors.form', [
            ...$this->layoutData($request->user()),
            'exhibitor' => new Exhibitor,
            'pageTitle' => 'Ajouter un exposant',
            'formAction' => route('commercial.exhibitors.store'),
            'formMethod' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $account = $request->validate(['password' => ['required', 'confirmed', Password::min(8)]]);
        $exhibitor = DB::transaction(function () use ($request, $validated, $account) {
            $exhibitor = $request->user()->exhibitors()->create($validated);
            $this->createAccount($exhibitor, $account['password']);
            return $exhibitor;
        });

        return redirect()->route('commercial.exhibitors.show', $exhibitor)->with('status', 'Exposant ajouté avec succès.');
    }

    public function createAccess(Request $request, Exhibitor $exhibitor): RedirectResponse
    {
        $exhibitor = $this->ownedBy($request, $exhibitor);
        abort_if($exhibitor->user_id, 409);
        $validated = $request->validate(['password' => ['required', 'confirmed', Password::min(8)]]);
        DB::transaction(fn () => $this->createAccount($exhibitor, $validated['password']));
        return back()->with('status', 'Compte exposant créé.');
    }

    public function toggleAccess(Request $request, Exhibitor $exhibitor): RedirectResponse
    {
        $exhibitor = $this->ownedBy($request, $exhibitor);
        abort_unless($exhibitor->user, 404);
        $exhibitor->user->is_active = ! $exhibitor->user->is_active;
        $exhibitor->user->save();
        return back()->with('status', $exhibitor->user->is_active ? 'Accès activé.' : 'Accès désactivé.');
    }

    private function createAccount(Exhibitor $exhibitor, string $password): void
    {
        abort_if($exhibitor->user_id, 409);
        $user = User::query()->create(['name' => $exhibitor->contact_name, 'email' => $exhibitor->email, 'password' => $password, 'role' => UserRole::Exposant, 'is_active' => true]);
        $exhibitor->user()->associate($user)->save();
    }

    public function show(Request $request, Exhibitor $exhibitor): View
    {
        $exhibitor = $this->ownedBy($request, $exhibitor);

        return view('commercial.exhibitors.show', [
            ...$this->layoutData($request->user()),
            'exhibitor' => $exhibitor,
            'accessStatus' => ! $exhibitor->user ? 'Sans accès' : ($exhibitor->user->is_active ? 'Accès actif' : 'Désactivé'),
        ]);
    }

    public function edit(Request $request, Exhibitor $exhibitor): View
    {
        $exhibitor = $this->ownedBy($request, $exhibitor);

        return view('commercial.exhibitors.form', [
            ...$this->layoutData($request->user()),
            'exhibitor' => $exhibitor,
            'pageTitle' => 'Modifier un exposant',
            'formAction' => route('commercial.exhibitors.update', $exhibitor),
            'formMethod' => 'put',
        ]);
    }

    
public function update(
    Request $request,
    Exhibitor $exhibitor
): RedirectResponse {
    $exhibitor = $this->ownedBy($request, $exhibitor);
    $validated = $this->validated($request);

    DB::transaction(function () use ($exhibitor, $validated) {
        $locked = Exhibitor::query()
            ->lockForUpdate()
            ->findOrFail($exhibitor->id);

        $used = $locked->badges()->count();

        if ($validated['authorized_badges'] < $used) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'authorized_badges' =>
                    "Impossible de réduire le quota : {$used} badges sont déjà enregistrés.",
            ]);
        }

        $locked->update($validated);
    });

    return redirect()
        ->route('commercial.exhibitors.show', $exhibitor)
        ->with('status', 'Informations de l’exposant mises à jour.');
}


    private function ownedBy(Request $request, Exhibitor $exhibitor): Exhibitor
    {
        abort_unless((int) $exhibitor->commercial_id === (int) $request->user()->id, 404);

        return $exhibitor;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'stand_number' => ['required', 'string', 'max:50'],
            'area_sqm' => ['required', 'numeric', 'gt:0', 'max:999999.99'],
            'authorized_badges' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);
    }

    private function layoutData($commercial): array
    {
        return [
            'commercial' => $commercial,
            'commercialInitials' => collect(preg_split('/\s+/', trim($commercial->name)))
                ->filter()->take(2)
                ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
                ->implode('') ?: 'C',
        ];
    }
}

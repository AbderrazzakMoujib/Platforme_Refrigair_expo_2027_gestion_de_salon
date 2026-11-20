<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Exhibitor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class CommercialExhibitorController extends Controller
{
    public function index(Request $request): View
    {
        $commercial = $request->user();

        $baseQuery = $commercial->exhibitors();

        $totalExhibitors = (clone $baseQuery)->count();

        $activeExhibitors = (clone $baseQuery)
            ->whereHas('user', fn ($query) =>
                $query->where('is_active', true)
            )->count();

        $inactiveExhibitors = (clone $baseQuery)
            ->whereHas('user', fn ($query) =>
                $query->where('is_active', false)
            )->count();

        $withoutAccess = (clone $baseQuery)
            ->whereDoesntHave('user')
            ->count();

        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', '');

        $exhibitors = (clone $baseQuery)
            ->with('user')
            ->withCount('badges')

            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('stand_number', 'like', "%{$search}%");
                });
            })

            ->when($status === 'active', function ($query) {
                $query->whereHas('user', fn ($user) =>
                    $user->where('is_active', true)
                );
            })

            ->when($status === 'inactive', function ($query) {
                $query->whereHas('user', fn ($user) =>
                    $user->where('is_active', false)
                );
            })

            ->when($status === 'no_access', function ($query) {
                $query->whereDoesntHave('user');
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('commercial.exhibitors.index', [
            ...$this->layoutData($commercial),
            'exhibitors' => $exhibitors,
            'totalExhibitors' => $totalExhibitors,
            'activeExhibitors' => $activeExhibitors,
            'inactiveExhibitors' => $inactiveExhibitors,
            'withoutAccess' => $withoutAccess,
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

        $account = $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ]);

        unset($validated['logo']);

        $logoPath = $this->uploadLogo($request);

        if ($logoPath !== null) {
            $validated['logo'] = $logoPath;
        }

        try {
            $exhibitor = DB::transaction(function () use (
                $request,
                $validated,
                $account
            ) {
                $exhibitor = $request->user()
                    ->exhibitors()
                    ->create($validated);

                $this->createAccount(
                    $exhibitor,
                    $account['password']
                );

                return $exhibitor;
            });
        } catch (Throwable $exception) {
            if ($logoPath !== null) {
                Storage::disk('public')->delete($logoPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('commercial.exhibitors.show', $exhibitor)
            ->with('status', 'Exposant ajouté avec succès.');
    }

    public function show(
        Request $request,
        Exhibitor $exhibitor
    ): View {
        $exhibitor = $this->ownedBy($request, $exhibitor);

        $exhibitor->load('user');
        $exhibitor->loadCount('badges');

        return view('commercial.exhibitors.show', [
            ...$this->layoutData($request->user()),
            'exhibitor' => $exhibitor,
            'accessStatus' => ! $exhibitor->user
                ? 'Sans accès'
                : ($exhibitor->user->is_active
                    ? 'Accès actif'
                    : 'Désactivé'),
        ]);
    }

    public function edit(
        Request $request,
        Exhibitor $exhibitor
    ): View {
        $exhibitor = $this->ownedBy($request, $exhibitor);

        $exhibitor->loadCount('badges');

        return view('commercial.exhibitors.form', [
            ...$this->layoutData($request->user()),
            'exhibitor' => $exhibitor,
            'pageTitle' => 'Modifier un exposant',
            'formAction' => route(
                'commercial.exhibitors.update',
                $exhibitor
            ),
            'formMethod' => 'put',
        ]);
    }

    public function update(
        Request $request,
        Exhibitor $exhibitor
    ): RedirectResponse {
        $exhibitor = $this->ownedBy($request, $exhibitor);

        $validated = $this->validated($request);

        unset($validated['logo']);

        $newLogo = $this->uploadLogo($request);

        if ($newLogo !== null) {
            $validated['logo'] = $newLogo;
        }

        try {
            DB::transaction(function () use (
                $request,
                $exhibitor,
                $validated
            ) {
                $locked = Exhibitor::query()
                    ->where('commercial_id', $request->user()->id)
                    ->lockForUpdate()
                    ->findOrFail($exhibitor->id);

                $used = $locked->badges()->count();

                if ($validated['authorized_badges'] < $used) {
                    throw ValidationException::withMessages([
                        'authorized_badges' =>
                            "Impossible de réduire le quota : {$used} badges sont déjà enregistrés.",
                    ]);
                }

                $locked->update($validated);
            });

        } catch (Throwable $exception) {
            if ($newLogo !== null) {
                Storage::disk('public')->delete($newLogo);
            }

            throw $exception;
        }

        return redirect()
            ->route('commercial.exhibitors.show', $exhibitor)
            ->with('status', 'Informations de l’exposant mises à jour.');
    }

    public function createAccess(
        Request $request,
        Exhibitor $exhibitor
    ): RedirectResponse {
        $exhibitor = $this->ownedBy($request, $exhibitor);

        abort_if($exhibitor->user_id, 409);

        $validated = $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ]);

        DB::transaction(fn () =>
            $this->createAccount(
                $exhibitor,
                $validated['password']
            )
        );

        return back()->with(
            'status',
            'Compte exposant créé.'
        );
    }

    public function toggleAccess(
        Request $request,
        Exhibitor $exhibitor
    ): RedirectResponse {
        $exhibitor = $this->ownedBy($request, $exhibitor);

        abort_unless($exhibitor->user, 404);

        $exhibitor->user->is_active =
            ! $exhibitor->user->is_active;

        $exhibitor->user->save();

        return back()->with(
            'status',
            $exhibitor->user->is_active
                ? 'Accès activé.'
                : 'Accès désactivé.'
        );
    }

    private function uploadLogo(Request $request): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        $path = $request->file('logo')->store(
            'exhibitors/logos',
            'public'
        );

        if ($path === false) {
            throw new RuntimeException(
                'Impossible d’enregistrer le logo.'
            );
        }

        return $path;
    }

    private function createAccount(
        Exhibitor $exhibitor,
        string $password
    ): void {
        abort_if($exhibitor->user_id, 409);

        $user = User::query()->create([
            'name' => $exhibitor->contact_name,
            'email' => $exhibitor->email,
            'password' => $password,
            'role' => UserRole::Exposant,
            'is_active' => true,
        ]);

        $exhibitor->user()->associate($user)->save();
    }

    private function ownedBy(
        Request $request,
        Exhibitor $exhibitor
    ): Exhibitor {
        abort_unless(
            (int) $exhibitor->commercial_id ===
            (int) $request->user()->id,
            404
        );

        return $exhibitor;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'company_name' => [
                'required', 'string', 'max:255',
            ],

            'contact_name' => [
                'required', 'string', 'max:255',
            ],

            'email' => [
                'required', 'email', 'max:255',
            ],

            'phone' => [
                'required', 'string', 'max:40',
            ],

            'stand_number' => [
                'required', 'string', 'max:50',
            ],

            'area_sqm' => [
                'required', 'numeric', 'gt:0', 'max:999999.99',
            ],

            'authorized_badges' => [
                'required', 'integer', 'min:0', 'max:100000',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:max_width=4096,max_height=4096',
            ],
        ]);
    }

    private function layoutData($commercial): array
    {
        return [
            'commercial' => $commercial,

            'commercialInitials' => collect(
                preg_split('/\s+/', trim($commercial->name))
            )
                ->filter()
                ->take(2)
                ->map(fn (string $part) =>
                    mb_strtoupper(mb_substr($part, 0, 1))
                )
                ->implode('') ?: 'C',
        ];
    }
}
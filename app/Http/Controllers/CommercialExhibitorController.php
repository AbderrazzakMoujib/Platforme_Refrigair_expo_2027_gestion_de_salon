<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Exhibitor;
use App\Models\User;
use App\Models\CommercialInvitation;
use App\Services\AccountInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
            ->with('user.latestInvitation')
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

        $request->validate(['email' => [Rule::unique('users', 'email')]]);

        unset($validated['logo']);

        $logoPath = $this->uploadLogo($request);

        if ($logoPath !== null) {
            $validated['logo'] = $logoPath;
        }

        try {
            [$exhibitor, $issued] = DB::transaction(function () use (
                $request,
                $validated
            ) {
                $exhibitor = $request->user()
                    ->exhibitors()
                    ->create($validated);

                $user = $this->createAccount($exhibitor);
                $issued = app(AccountInvitationService::class)->issueFor($user);

                return [$exhibitor, $issued];
            });
        } catch (Throwable $exception) {
            if ($logoPath !== null) {
                Storage::disk('public')->delete($logoPath);
            }

            throw $exception;
        }

        $sent = app(AccountInvitationService::class)->deliver($issued['invitation'], $issued['token']);

        return redirect()
            ->route('commercial.exhibitors.show', $exhibitor)
            ->with($sent ? 'status' : 'warning', $sent
                ? 'Exposant ajouté. Son invitation a été envoyée par courriel.'
                : 'Exposant créé, mais le courriel d’invitation n’a pas pu être envoyé.');
    }

    public function show(
        Request $request,
        Exhibitor $exhibitor
    ): View {
        $exhibitor = $this->ownedBy($request, $exhibitor);

        $exhibitor->load('user.latestInvitation');
        $exhibitor->loadCount('badges');

        return view('commercial.exhibitors.show', [
            ...$this->layoutData($request->user()),
            'exhibitor' => $exhibitor,
            'accessStatus' => ! $exhibitor->user ? 'Sans accès' : CommercialInvitation::statusFor($exhibitor->user),
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
        $exhibitor->loadMissing('user');
        if ($exhibitor->user && $exhibitor->user->password === null) {
            $request->validate([
                'email' => [Rule::unique('users', 'email')->ignore($exhibitor->user->id)],
            ]);
        }

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

                $account = $locked->user()->lockForUpdate()->first();
                if ($account && $account->password === null) {
                    $identityChanged = $account->email !== $locked->email || $account->name !== $locked->contact_name;
                    $account->forceFill(['email' => $locked->email, 'name' => $locked->contact_name])->save();
                    if ($identityChanged) {
                        app(AccountInvitationService::class)->revokeOutstanding($account);
                    }
                }
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

        $issued = DB::transaction(function () use ($exhibitor) {
            $user = $this->createAccount($exhibitor);
            return app(AccountInvitationService::class)->issueFor($user);
        });
        $sent = app(AccountInvitationService::class)->deliver($issued['invitation'], $issued['token']);
        return back()->with($sent ? 'status' : 'warning', $sent
            ? 'Compte exposant créé et invitation envoyée.'
            : 'Compte exposant créé, mais le courriel n’a pas pu être envoyé.');
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
        if (! $exhibitor->user->is_active) {
            app(AccountInvitationService::class)->revokeOutstanding($exhibitor->user);
        }

        return back()->with(
            'status',
            $exhibitor->user->is_active
                ? 'Accès activé.'
                : 'Accès désactivé.'
        );
    }

    public function resendInvitation(Request $request, Exhibitor $exhibitor, AccountInvitationService $invitations): RedirectResponse
    {
        $exhibitor = $this->ownedBy($request, $exhibitor);
        abort_unless($exhibitor->user, 404);
        $issued = $invitations->prepareResend($exhibitor->user);
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

    private function createAccount(Exhibitor $exhibitor): User {
        abort_if($exhibitor->user_id, 409);

        $user = User::query()->create([
            'name' => $exhibitor->contact_name,
            'email' => $exhibitor->email,
            'password' => null,
            'role' => UserRole::Exposant,
            'is_active' => true,
        ]);

        $exhibitor->user()->associate($user)->save();
        return $user;
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

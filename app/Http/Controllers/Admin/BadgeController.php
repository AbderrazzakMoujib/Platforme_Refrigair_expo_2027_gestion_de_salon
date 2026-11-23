<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\BadgeActivityLog;
use App\Models\Exhibitor;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BadgeController extends Controller
{
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
                ->map(fn (string $part) =>
                    mb_strtoupper(mb_substr($part, 0, 1))
                )
                ->implode('') ?: 'A',
        ];
    }

    private function badgeRules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'function' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
        ];
    }

    private function log(
        Badge $badge,
        ?int $userId,
        string $action,
        array $details = []
    ): void {
        BadgeActivityLog::create([
            'badge_id' => $badge->id,
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
        ]);
    }

    private function qrDataUri(Badge $badge): string
    {
        $url = route('admin.badges.verify', [
            'token' => $badge->qr_token,
        ]);

        $qrCode = new QrCode(
            data: $url,
            size: 260,
            margin: 10
        );

        return (new SvgWriter())
            ->write($qrCode)
            ->getDataUri();
    }

    /*
    |--------------------------------------------------------------------------
    | Liste des badges
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'exhibitor_id' => [
                'nullable',
                'integer',
                'exists:exhibitors,id',
            ],
            'status' => [
                'nullable',
                Rule::in(['active', 'inactive']),
            ],
        ]);

        $query = Badge::query()
            ->with([
                'exhibitor:id,company_name,commercial_id,authorized_badges,logo',
                'exhibitor.commercial:id,name',
            ]);

        if (!empty($filters['search'])) {

            $term = trim($filters['search']);

            $query->where(function ($q) use ($term) {

                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('function', 'like', "%{$term}%")
                    ->orWhereHas('exhibitor', function ($exhibitor) use ($term) {

                        $exhibitor->where(
                            'company_name',
                            'like',
                            "%{$term}%"
                        );

                    });

            });

        }

        if (!empty($filters['exhibitor_id'])) {

            $query->where(
                'exhibitor_id',
                $filters['exhibitor_id']
            );

        }

        if (!empty($filters['status'])) {

            $query->where(
                'status',
                $filters['status']
            );

        }

        $totalAuthorized = (int) Exhibitor::sum('authorized_badges');

        $totalBadges = Badge::count();

        $activeBadges = Badge::where('status', 'active')->count();

        return view('admin.badges.index', [

            ...$this->layoutData($request),

            'badges' => $query
                ->latest()
                ->paginate(12)
                ->withQueryString(),

            'exhibitors' => Exhibitor::query()
                ->orderBy('company_name')
                ->get([
                    'id',
                    'company_name',
                    'authorized_badges',
                    'logo',
                ]),

            'totalAuthorized' => $totalAuthorized,

            'totalBadges' => $totalBadges,

            'activeBadges' => $activeBadges,

            'remainingBadges' => max(
                0,
                $totalAuthorized - $totalBadges
            ),

            'percentage' => $totalAuthorized > 0
                ? round($totalBadges / $totalAuthorized * 100, 1)
                : 0,

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Formulaire de création
    |--------------------------------------------------------------------------
    */

    public function create(Request $request): View
    {
        return view('admin.badges.form', [

            ...$this->layoutData($request),

            'badge' => new Badge([
                'status' => 'active',
            ]),

            'exhibitors' => Exhibitor::query()
                ->withCount('badges')
                ->orderBy('company_name')
                ->get(),

            'pageTitle' => 'Créer un badge',

            'formAction' => route('admin.badges.store'),

            'formMethod' => null,

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Création d'un badge
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([

            'exhibitor_id' => [
                'required',
                'integer',
                'exists:exhibitors,id',
            ],

            ...$this->badgeRules(),

        ]);

        $badge = DB::transaction(function () use ($request, $data) {

            $exhibitor = Exhibitor::query()
                ->lockForUpdate()
                ->findOrFail($data['exhibitor_id']);

            if (
                $exhibitor->badges()->count()
                >= $exhibitor->authorized_badges
            ) {

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'exhibitor_id' =>
                        'Quota de badges atteint pour cet exposant.',
                ]);

            }

            $badge = $exhibitor->badges()->create(
                collect($data)
                    ->except('exhibitor_id')
                    ->all()
            );

            $this->log(
                $badge,
                $request->user()->id,
                'created',
                ['source' => 'admin']
            );

            return $badge;

        });

        return redirect()
            ->route('admin.badges.show', $badge)
            ->with('status', 'Badge créé avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | Détail d'un badge
    |--------------------------------------------------------------------------
    */

    public function show(Request $request, Badge $badge): View
    {
        $badge->load([
            'exhibitor.commercial:id,name',
        ]);

        return view('admin.badges.show', [

            ...$this->layoutData($request),

            'badge' => $badge,

            'qrImage' => $this->qrDataUri($badge),

            'activities' => $badge->activities()
                ->with('user:id,name')
                ->latest()
                ->paginate(10),

            'usedQuota' => $badge->exhibitor
                ->badges()
                ->count(),

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Formulaire de modification
    |--------------------------------------------------------------------------
    */

    public function edit(Request $request, Badge $badge): View
    {
        return view('admin.badges.form', [

            ...$this->layoutData($request),

            'badge' => $badge->load('exhibitor'),

            'exhibitors' => collect(),

            'pageTitle' => 'Modifier un badge',

            'formAction' => route('admin.badges.update', $badge),

            'formMethod' => 'PUT',

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Modification du badge
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Badge $badge
    ): RedirectResponse {

        $data = $request->validate(
            $this->badgeRules()
        );

        DB::transaction(function () use ($request, $badge, $data) {

            $before = $badge->only(array_keys($data));

            $badge->update($data);

            $this->log(
                $badge,
                $request->user()->id,
                'updated',
                [
                    'before' => $before,
                    'after' => $data,
                ]
            );

        });

        return redirect()
            ->route('admin.badges.show', $badge)
            ->with('status', 'Badge modifié avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | Activation / Désactivation
    |--------------------------------------------------------------------------
    */

    public function toggle(
        Request $request,
        Badge $badge
    ): RedirectResponse {

        DB::transaction(function () use ($request, $badge) {

            $badge->update([

                'status' => $badge->status === 'active'
                    ? 'inactive'
                    : 'active',

            ]);

            $this->log(

                $badge,

                $request->user()->id,

                $badge->status === 'active'
                    ? 'activated'
                    : 'deactivated'

            );

        });

        return back()->with(

            'status',

            $badge->status === 'active'
                ? 'Badge réactivé.'
                : 'Badge désactivé.'

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Archivage du badge
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        Badge $badge
    ): RedirectResponse {

        DB::transaction(function () use ($request, $badge) {

            $this->log(

                $badge,

                $request->user()->id,

                'archived',

                [
                    'name' => $badge->full_name,
                    'reference' => $badge->reference,
                ]

            );

            $badge->delete();

        });

        return redirect()
            ->route('admin.badges.index')
            ->with('status', 'Badge archivé avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | Gestion du quota
    |--------------------------------------------------------------------------
    */

    public function updateQuota(
        Request $request,
        Exhibitor $exhibitor
    ): RedirectResponse {

        $validated = $request->validate([

            'authorized_badges' => [
                'required',
                'integer',
                'min:0',
                'max:100000',
            ],

        ]);

        DB::transaction(function () use ($exhibitor, $validated) {

            $locked = Exhibitor::query()
                ->lockForUpdate()
                ->findOrFail($exhibitor->id);

            $used = $locked->badges()->count();

            if ($validated['authorized_badges'] < $used) {

                throw \Illuminate\Validation\ValidationException::withMessages([

                    'authorized_badges' =>
                        "Quota impossible : {$used} badges existent déjà.",

                ]);

            }

            $locked->update([

                'authorized_badges' =>
                    $validated['authorized_badges'],

            ]);

        });

        return back()->with(
            'status',
            'Quota de badges mis à jour.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Vérification du QR Code
    |--------------------------------------------------------------------------
    */

    public function verify(
        Request $request,
        string $token
    ): View {

        $badge = Badge::query()
            ->with('exhibitor')
            ->where('qr_token', $token)
            ->firstOrFail();

        return view('admin.badges.verify', [

            ...$this->layoutData($request),

            'badge' => $badge,

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Impression individuelle
    |--------------------------------------------------------------------------
    */

    public function print(
        Request $request,
        Badge $badge
    ): View {

        $badge->load('exhibitor');

        return view('admin.badges.print', [

            'badge' => $badge,

            'qrImage' => $this->qrDataUri($badge),

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Impression groupée
    |--------------------------------------------------------------------------
    */

    public function printSelected(Request $request): View
    {
        $data = $request->validate([

            'ids' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:badges,id',
            ],

        ]);

        $badges = Badge::query()
            ->with('exhibitor')
            ->whereIn('id', $data['ids'])
            ->orderBy('id')
            ->get();

        if ($badges->count() !== count($data['ids'])) {

            abort(404);

        }

        $items = $badges->map(fn (Badge $badge) => [

            'badge' => $badge,

            'qrImage' => $this->qrDataUri($badge),

        ]);

        return view('admin.badges.print-bulk', [

            'items' => $items,

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Export CSV
    |--------------------------------------------------------------------------
    */

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([

            'exhibitor_id' => [
                'nullable',
                'integer',
                'exists:exhibitors,id',
            ],

            'status' => [
                'nullable',
                Rule::in(['active', 'inactive']),
            ],

        ]);

        $query = Badge::query()
            ->with('exhibitor:id,company_name')
            ->orderBy('id');

        if (!empty($filters['exhibitor_id'])) {

            $query->where(
                'exhibitor_id',
                $filters['exhibitor_id']
            );

        }

        if (!empty($filters['status'])) {

            $query->where('status', $filters['status']);

        }

        return response()->streamDownload(

            function () use ($query) {

                $output = fopen('php://output', 'w');

                fwrite($output, "\xEF\xBB\xBF");

                fputcsv($output, [
                    'Référence',
                    'Nom',
                    'Prénom',
                    'Société',
                    'Fonction',
                    'Email',
                    'Téléphone',
                    'Statut',
                    'Créé le',
                ], ';');

                $safe = function ($value): string {

                    $text = (string) ($value ?? '');

                    // Protection contre les formules Excel.
                    if (preg_match('/^[\s]*[=+\-@]/u', $text)) {

                        return "'".$text;

                    }

                    return $text;

                };

                foreach ($query->lazyById(200) as $badge) {

                    fputcsv($output, [

                        $safe($badge->reference),

                        $safe($badge->last_name),

                        $safe($badge->first_name),

                        $safe($badge->exhibitor?->company_name),

                        $safe($badge->function),

                        $safe($badge->email),

                        $safe($badge->phone),

                        $badge->status === 'active'
                            ? 'Actif'
                            : 'Inactif',

                        $badge->created_at?->format('Y-m-d H:i') 
                            ? $badge->created_at->format('d/m/Y H:i')
                            : null,

                    ], ';');

                }

                fclose($output);

            },

            'badges-refrigair-'.now()->format('Ymd-His').'.csv',

            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]

        );
    }
}

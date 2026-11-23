<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\BadgeActivityLog;
use App\Models\Exhibitor;
use App\Models\User;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommercialBadgeController extends Controller
{
    public const ACTION_PRINTED = 'printed';

    private const TABS = ['exhibitors', 'badges', 'history'];

    private const EXHIBITOR_STATUSES = ['complete', 'partial', 'pending', 'no_quota'];

    private const MAX_BULK_PRINT = 200;

    /** Nombre de badges non archivés d'un exposant, utilisable dans un WHERE. */
    private const BADGE_COUNT_SQL = '(select count(*) from badges where badges.exhibitor_id = exhibitors.id and badges.deleted_at is null)';

    public function index(Request $request): View
    {
        $commercial = $request->user();

        $filters = $request->validate([
            'tab' => ['nullable', Rule::in(self::TABS)],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(self::EXHIBITOR_STATUSES)],
            'exhibitor_id' => ['nullable', 'integer'],
        ]);
        $tab = $filters['tab'] ?? 'exhibitors';
        $search = trim((string) ($filters['search'] ?? ''));

        // --- Statistiques globales (toujours limitées au commercial connecté) ---
        $totalAuthorized = (int) $commercial->exhibitors()->sum('authorized_badges');
        $ownBadges = fn (): Builder => $this->ownBadges($commercial);
        $totalBadges = $ownBadges()->count();
        $printedBadges = $ownBadges()
            ->whereHas('activities', fn ($query) => $query->where('action', self::ACTION_PRINTED))
            ->count();

        $data = [
            ...$this->layoutData($commercial),
            'tab' => $tab,
            'filters' => [
                'search' => $search,
                'status' => $filters['status'] ?? null,
                'exhibitor_id' => $filters['exhibitor_id'] ?? null,
            ],
            'totalAuthorized' => $totalAuthorized,
            'totalBadges' => $totalBadges,
            'newBadgesThisMonth' => $ownBadges()->where('created_at', '>=', now()->startOfMonth())->count(),
            'printedBadges' => $printedBadges,
            'remainingBadges' => max(0, $totalAuthorized - $totalBadges),
            'quotaUsage' => $totalAuthorized > 0 ? min(100, round($totalBadges / $totalAuthorized * 100, 1)) : 0,
            'recentActivities' => $this->ownActivities($commercial)->latest()->latest('id')->limit(5)->get(),
            'exhibitorOptions' => $commercial->exhibitors()->orderBy('company_name')->get(['id', 'company_name']),
        ];

        if ($tab === 'badges') {
            $data['badges'] = $ownBadges()
                ->with('exhibitor:id,company_name,stand_number,logo')
                ->withMax(['activities as last_printed_at' => fn ($query) => $query->where('action', self::ACTION_PRINTED)], 'created_at')
                ->when($data['filters']['exhibitor_id'], fn ($query, $id) => $query->where('exhibitor_id', $id))
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('function', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->latest('id')
                ->paginate(12)
                ->withQueryString();
        } elseif ($tab === 'history') {
            $data['activities'] = $this->ownActivities($commercial)
                ->latest()
                ->latest('id')
                ->paginate(15)
                ->withQueryString();
        } else {
            $data['exhibitors'] = $this->filteredExhibitors($commercial, $search, $data['filters']['status'])
                ->paginate(10)
                ->withQueryString();
        }

        return view('commercial.badges.index', $data);
    }

    public function updateQuota(Request $request, Exhibitor $exhibitor): RedirectResponse
    {
        $this->ensureOwnsExhibitor($request->user(), $exhibitor);

        $validated = $request->validate([
            'authorized_badges' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        DB::transaction(function () use ($exhibitor, $validated): void {
            $locked = Exhibitor::query()->lockForUpdate()->findOrFail($exhibitor->id);
            $used = $locked->badges()->count();

            if ($validated['authorized_badges'] < $used) {
                throw ValidationException::withMessages([
                    'authorized_badges' => "Quota impossible pour {$locked->company_name} : {$used} badges sont déjà enregistrés.",
                ]);
            }

            $locked->update(['authorized_badges' => $validated['authorized_badges']]);
        });

        return back()->with('status', "Quota de badges mis à jour pour {$exhibitor->company_name}.");
    }

    public function print(Request $request, Badge $badge): View
    {
        $badge->load('exhibitor');
        $this->ensureOwnsExhibitor($request->user(), $badge->exhibitor);
        abort_unless($badge->status === 'active', 422, 'Ce badge est désactivé et ne peut pas être imprimé.');

        $this->logPrinted(collect([$badge]), $request->user(), 'single');

        return view('admin.badges.print', [
            'badge' => $badge,
            'qrImage' => $this->qrDataUri($badge),
        ]);
    }

    public function printExhibitor(Request $request, Exhibitor $exhibitor): View|RedirectResponse
    {
        $this->ensureOwnsExhibitor($request->user(), $exhibitor);

        $badges = $exhibitor->badges()
            ->where('status', 'active')
            ->orderBy('id')
            ->limit(self::MAX_BULK_PRINT)
            ->get()
            ->each(fn (Badge $badge) => $badge->setRelation('exhibitor', $exhibitor));

        if ($badges->isEmpty()) {
            return back()->with('status', "Aucun badge actif à imprimer pour {$exhibitor->company_name}.");
        }

        $this->logPrinted($badges, $request->user(), 'exhibitor');

        return view('admin.badges.print-bulk', [
            'items' => $badges->map(fn (Badge $badge) => [
                'badge' => $badge,
                'qrImage' => $this->qrDataUri($badge),
            ]),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $commercial = $request->user();
        $filters = $request->validate([
            'exhibitor_id' => ['nullable', 'integer'],
        ]);

        $query = $this->ownBadges($commercial)
            ->with('exhibitor:id,company_name,stand_number')
            ->withMax(['activities as last_printed_at' => fn ($q) => $q->where('action', self::ACTION_PRINTED)], 'created_at')
            ->when($filters['exhibitor_id'] ?? null, fn ($q, $id) => $q->where('exhibitor_id', $id))
            ->orderBy('id');

        // Évite l'interprétation d'une cellule comme formule Excel.
        $safe = function ($value): string {
            $text = (string) ($value ?? '');

            return preg_match('/^\s*[=+\-@]/u', $text) ? "'".$text : $text;
        };

        return response()->streamDownload(function () use ($query, $safe): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Référence', 'Nom', 'Prénom', 'Société', 'Stand', 'Fonction', 'Email', 'Téléphone', 'Statut', 'Imprimé', 'Créé le'], ';');

            foreach ($query->lazyById(200) as $badge) {
                fputcsv($output, [
                    $safe($badge->reference),
                    $safe($badge->last_name),
                    $safe($badge->first_name),
                    $safe($badge->exhibitor?->company_name),
                    $safe($badge->exhibitor?->stand_number),
                    $safe($badge->function),
                    $safe($badge->email),
                    $safe($badge->phone),
                    $badge->status === 'active' ? 'Actif' : 'Inactif',
                    $badge->last_printed_at ? 'Oui' : 'Non',
                    $badge->created_at?->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($output);
        }, 'badges-commercial-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** Badges non archivés des exposants du commercial. */
    private function ownBadges(User $commercial): Builder
    {
        return Badge::query()->whereHas(
            'exhibitor',
            fn ($query) => $query->where('commercial_id', $commercial->id)
        );
    }

    /** Journal des badges du commercial, y compris les badges archivés. */
    private function ownActivities(User $commercial): Builder
    {
        return BadgeActivityLog::query()
            ->whereHas('badge', fn ($query) => $query
                ->withTrashed()
                ->whereHas('exhibitor', fn ($q) => $q->where('commercial_id', $commercial->id)))
            ->with([
                'badge' => fn ($query) => $query->withTrashed()->with('exhibitor:id,company_name'),
                'user:id,name',
            ]);
    }

    private function filteredExhibitors(User $commercial, string $search, ?string $status)
    {
        $count = self::BADGE_COUNT_SQL;

        return $commercial->exhibitors()
            ->withCount([
                'badges',
                'badges as active_badges_count' => fn ($query) => $query->where('status', 'active'),
                'badges as printed_badges_count' => fn ($query) => $query->whereHas(
                    'activities',
                    fn ($q) => $q->where('action', self::ACTION_PRINTED)
                ),
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('stand_number', 'like', "%{$search}%");
                });
            })
            ->when($status === 'no_quota', fn ($q) => $q->where('authorized_badges', 0))
            ->when($status === 'pending', fn ($q) => $q->where('authorized_badges', '>', 0)->whereRaw("{$count} = 0"))
            ->when($status === 'partial', fn ($q) => $q->whereRaw("{$count} > 0")->whereRaw("{$count} < exhibitors.authorized_badges"))
            ->when($status === 'complete', fn ($q) => $q->where('authorized_badges', '>', 0)->whereRaw("{$count} >= exhibitors.authorized_badges"))
            ->orderBy('company_name')
            ->orderBy('id');
    }

    private function ensureOwnsExhibitor(User $commercial, ?Exhibitor $exhibitor): void
    {
        abort_unless($exhibitor && (int) $exhibitor->commercial_id === (int) $commercial->id, 404);
    }

    private function logPrinted($badges, User $commercial, string $mode): void
    {
        $now = now();

        BadgeActivityLog::query()->insert($badges->map(fn (Badge $badge) => [
            'badge_id' => $badge->id,
            'user_id' => $commercial->id,
            'action' => self::ACTION_PRINTED,
            'details' => json_encode(['source' => 'commercial', 'mode' => $mode]),
            'created_at' => $now,
            'updated_at' => $now,
        ])->all());
    }

    private function qrDataUri(Badge $badge): string
    {
        $qrCode = new QrCode(
            data: route('admin.badges.verify', ['token' => $badge->qr_token]),
            size: 260,
            margin: 10
        );

        return (new SvgWriter)->write($qrCode)->getDataUri();
    }

    private function layoutData(User $commercial): array
    {
        return [
            'commercial' => $commercial,
            'commercialInitials' => collect(preg_split('/\s+/', trim($commercial->name)))
                ->filter()
                ->take(2)
                ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
                ->implode('') ?: 'C',
        ];
    }
}

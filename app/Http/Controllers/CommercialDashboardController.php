<?php

namespace App\Http\Controllers;

use App\Models\Exhibitor;
use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CommercialDashboardController extends Controller
{
    public const STATUS_COMPLETE = 'complete';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_PENDING = 'pending';

    public const STATUS_NO_QUOTA = 'no_quota';

    private const RECENT_LIMIT = 5;

    private const FOLLOW_UP_LIMIT = 5;

    /** Périodes proposées pour la courbe des demandes (en jours). */
    public const TREND_PERIODS = [7, 30, 90];

    private const DEFAULT_TREND_DAYS = 30;

    public function __invoke(Request $request): View
    {
        $commercial = $request->user();

        $trendDays = (int) $request->query('period', self::DEFAULT_TREND_DAYS);
        if (! in_array($trendDays, self::TREND_PERIODS, true)) {
            $trendDays = self::DEFAULT_TREND_DAYS;
        }

        // Uniquement les exposants rattachés au commercial connecté.
        $exhibitors = $commercial->exhibitors()
            ->with('user:id,is_active')
            ->withCount('badges')
            ->get()
            ->each(function (Exhibitor $exhibitor): void {
                $exhibitor->setAttribute('badge_status', $this->badgeStatus($exhibitor));
                $exhibitor->setAttribute('badge_progress', $this->percentage(
                    $exhibitor->badges_count,
                    $exhibitor->authorized_badges
                ));
                $exhibitor->setAttribute('access_status', $this->accessStatus($exhibitor));
            });

        $totalAuthorizedBadges = (int) $exhibitors->sum('authorized_badges');
        $totalRegisteredBadges = (int) $exhibitors->sum('badges_count');
        $statusCounts = $exhibitors->countBy('badge_status');

        // Demandes d'assistance : même périmètre que CommercialSupportController@index.
        $tickets = fn () => SupportTicket::query()
            ->where('commercial_id', $commercial->id)
            ->whereHas('exhibitor', fn ($query) => $query->where('commercial_id', $commercial->id));

        $ticketStatusCounts = $tickets()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count) => (int) $count);

        $pendingTickets = $ticketStatusCounts->get('pending', 0);
        $inProgressTickets = $ticketStatusCounts->get('in_progress', 0);
        $resolvedTickets = $ticketStatusCounts->get('resolved', 0);

        return view('commercial.dashboard', [
            'commercial' => $commercial,
            'commercialInitials' => $this->initials($commercial->name),

            'newExhibitorsThisMonth' => $exhibitors
                ->filter(fn (Exhibitor $exhibitor): bool => (bool) $exhibitor->created_at?->gte(now()->startOfMonth()))
                ->count(),

            'pendingTickets' => $pendingTickets,
            'inProgressTickets' => $inProgressTickets,
            'resolvedTickets' => $resolvedTickets,
            'totalTickets' => $pendingTickets + $inProgressTickets + $resolvedTickets,
            'activeTickets' => $pendingTickets + $inProgressTickets,
            'newTicketsToday' => $tickets()->where('created_at', '>=', now()->startOfDay())->count(),
            'resolvedTicketsThisMonth' => $tickets()
                ->where('status', 'resolved')
                ->where('resolved_at', '>=', now()->startOfMonth())
                ->count(),
            'recentTickets' => $tickets()
                ->with('exhibitor:id,company_name,logo')
                ->latest()
                ->latest('id')
                ->limit(self::RECENT_LIMIT)
                ->get(),
            'ticketTrend' => $this->dailyTicketTrend($tickets(), $trendDays),
            'trendDays' => $trendDays,
            'trendPeriods' => self::TREND_PERIODS,
            'unreadNotificationsCount' => $commercial->unreadNotifications()->count(),

            'exhibitorsCount' => $exhibitors->count(),
            'totalAuthorizedBadges' => $totalAuthorizedBadges,
            'totalRegisteredBadges' => $totalRegisteredBadges,
            'remainingBadges' => max(0, $totalAuthorizedBadges - $totalRegisteredBadges),
            'badgeProgress' => $this->percentage($totalRegisteredBadges, $totalAuthorizedBadges),

            'completedExhibitors' => $statusCounts->get(self::STATUS_COMPLETE, 0),
            'partialExhibitors' => $statusCounts->get(self::STATUS_PARTIAL, 0),
            'pendingExhibitors' => $statusCounts->get(self::STATUS_PENDING, 0),
            'noQuotaExhibitors' => $statusCounts->get(self::STATUS_NO_QUOTA, 0),

            'activeAccessCount' => $exhibitors->where('access_status', 'active')->count(),
            'withoutAccessCount' => $exhibitors->where('access_status', 'none')->count(),
            'disabledAccessCount' => $exhibitors->where('access_status', 'disabled')->count(),

            'recentExhibitors' => $this->latestFirst($exhibitors)->take(self::RECENT_LIMIT)->values(),
            'followUpExhibitors' => $this->latestFirst($exhibitors->filter(
                fn (Exhibitor $exhibitor): bool => $exhibitor->badge_status === self::STATUS_PENDING
                    || $exhibitor->access_status !== 'active'
            ))->take(self::FOLLOW_UP_LIMIT)->values(),
        ]);
    }

    /**
     * Nombre de demandes créées par jour sur les $days derniers jours (jours vides inclus).
     *
     * @return Collection<int, array{date: Carbon, count: int}>
     */
    private function dailyTicketTrend(Builder $query, int $days): Collection
    {
        $start = now()->startOfDay()->subDays($days - 1);

        // Regroupement en PHP pour rester compatible SQLite / MySQL.
        $countsByDay = $query
            ->where('created_at', '>=', $start)
            ->pluck('created_at')
            ->countBy(fn ($createdAt) => Carbon::parse($createdAt)->toDateString());

        return collect(range(0, $days - 1))->map(function (int $offset) use ($start, $countsByDay): array {
            $date = $start->copy()->addDays($offset);

            return [
                'date' => $date,
                'count' => (int) $countsByDay->get($date->toDateString(), 0),
            ];
        });
    }

    private function badgeStatus(Exhibitor $exhibitor): string
    {
        return match (true) {
            $exhibitor->authorized_badges <= 0 => self::STATUS_NO_QUOTA,
            $exhibitor->badges_count === 0 => self::STATUS_PENDING,
            $exhibitor->badges_count >= $exhibitor->authorized_badges => self::STATUS_COMPLETE,
            default => self::STATUS_PARTIAL,
        };
    }

    private function accessStatus(Exhibitor $exhibitor): string
    {
        if (! $exhibitor->user) {
            return 'none';
        }

        return $exhibitor->user->is_active ? 'active' : 'disabled';
    }

    private function percentage(int $value, int $total): float
    {
        if ($total <= 0) {
            return 0;
        }

        return min(100, round($value / $total * 100, 1));
    }

    private function latestFirst(Collection $exhibitors): Collection
    {
        return $exhibitors->sortBy([
            ['created_at', 'desc'],
            ['id', 'desc'],
        ]);
    }

    private function initials(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'C';
    }
}

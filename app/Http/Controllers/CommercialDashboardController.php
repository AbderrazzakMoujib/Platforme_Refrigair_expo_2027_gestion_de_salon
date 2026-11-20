<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $commercial = $request->user();

        // Exposants appartenant au commercial connecté.
        $exhibitors = $commercial->exhibitors()
            ->withCount('badges')
            ->latest()
            ->get();

        $exhibitorsCount = $exhibitors->count();

        $newExhibitorsThisMonth = $exhibitors
            ->filter(fn ($exhibitor) =>
                $exhibitor->created_at?->gte(now()->startOfMonth())
            )->count();

        // Statistiques des badges.
        $totalAuthorizedBadges = $exhibitors->sum('authorized_badges');
        $totalRegisteredBadges = $exhibitors->sum('badges_count');

        $remainingBadges = $exhibitors->sum(fn ($exhibitor) =>
            max(0, $exhibitor->authorized_badges - $exhibitor->badges_count)
        );

        $completedExhibitors = $exhibitors->filter(fn ($exhibitor) =>
            $exhibitor->authorized_badges > 0 &&
            $exhibitor->badges_count >= $exhibitor->authorized_badges
        )->count();

        $pendingExhibitors = $exhibitors->filter(fn ($exhibitor) =>
            $exhibitor->badges_count === 0 &&
            $exhibitor->authorized_badges > 0
        )->count();

        $partialExhibitors = $exhibitors->filter(fn ($exhibitor) =>
            $exhibitor->badges_count > 0 &&
            $exhibitor->badges_count < $exhibitor->authorized_badges
        )->count();

        $badgeProgress = $totalAuthorizedBadges > 0
            ? min(100, round(
                ($totalRegisteredBadges / $totalAuthorizedBadges) * 100,
                1
            ))
            : 0;

        // Assistance : uniquement les tickets autorisés.
        $ticketsQuery = SupportTicket::query()
            ->where('commercial_id', $commercial->id)
            ->whereHas('exhibitor', fn ($query) =>
                $query->where('commercial_id', $commercial->id)
            );

        $statusCounts = (clone $ticketsQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pendingTickets = (int) ($statusCounts['pending'] ?? 0);
        $inProgressTickets = (int) ($statusCounts['in_progress'] ?? 0);
        $resolvedTickets = (int) ($statusCounts['resolved'] ?? 0);

        $activeTickets = $pendingTickets + $inProgressTickets;
        $totalTickets = $pendingTickets + $inProgressTickets + $resolvedTickets;

        $newTicketsToday = (clone $ticketsQuery)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        $resolvedTicketsThisMonth = (clone $ticketsQuery)
            ->where('status', 'resolved')
            ->where('resolved_at', '>=', now()->startOfMonth())
            ->count();

        $recentTickets = (clone $ticketsQuery)
            ->with('exhibitor:id,company_name')
            ->latest()
            ->limit(5)
            ->get();

        // Notifications non lues.
        $unreadNotifications = $commercial
            ->unreadNotifications()
            ->count();

        // Graphique : 7, 15 ou 30 derniers jours.
        $period = (int) $request->query('period', 30);

        if (! in_array($period, [7, 15, 30], true)) {
            $period = 30;
        }

        $startDate = now()
            ->startOfDay()
            ->subDays($period - 1);

        $dailyTickets = (clone $ticketsQuery)
            ->whereBetween('created_at', [
                $startDate,
                now()->endOfDay(),
            ])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'day');

        $chartLabels = [];
        $chartValues = [];

        for ($i = 0; $i < $period; $i++) {
            $date = $startDate->copy()->addDays($i);

            $chartLabels[] = $date->format('d/m');

            $chartValues[] = (int) (
                $dailyTickets[$date->toDateString()] ?? 0
            );
        }

        $ticketDistribution = [
            [
                'label' => 'En attente',
                'count' => $pendingTickets,
                'color' => '#8B5CF6',
            ],
            [
                'label' => 'En cours',
                'count' => $inProgressTickets,
                'color' => '#F59E0B',
            ],
            [
                'label' => 'Résolues',
                'count' => $resolvedTickets,
                'color' => '#10B981',
            ],
        ];

        $commercialInitials = collect(
            preg_split('/\s+/', trim($commercial->name))
        )
            ->filter()
            ->take(2)
            ->map(fn (string $part) =>
                mb_strtoupper(mb_substr($part, 0, 1))
            )
            ->implode('') ?: 'C';

        return view('commercial.dashboard', [
            'commercial' => $commercial,
            'commercialInitials' => $commercialInitials,

            'exhibitorsCount' => $exhibitorsCount,
            'newExhibitorsThisMonth' => $newExhibitorsThisMonth,
            'recentExhibitors' => $exhibitors->take(5),

            'totalAuthorizedBadges' => $totalAuthorizedBadges,
            'totalRegisteredBadges' => $totalRegisteredBadges,
            'remainingBadges' => $remainingBadges,
            'completedExhibitors' => $completedExhibitors,
            'pendingExhibitors' => $pendingExhibitors,
            'partialExhibitors' => $partialExhibitors,
            'badgeProgress' => $badgeProgress,

            'activeTickets' => $activeTickets,
            'pendingTickets' => $pendingTickets,
            'inProgressTickets' => $inProgressTickets,
            'resolvedTickets' => $resolvedTickets,
            'totalTickets' => $totalTickets,
            'newTicketsToday' => $newTicketsToday,
            'resolvedTicketsThisMonth' => $resolvedTicketsThisMonth,
            'recentTickets' => $recentTickets,
            'ticketDistribution' => $ticketDistribution,

            'unreadNotifications' => $unreadNotifications,

            'period' => $period,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
        ]);
    }
}

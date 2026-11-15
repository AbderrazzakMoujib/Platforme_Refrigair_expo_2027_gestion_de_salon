<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $commercial = $request->user();

        // Les exposants dyal commercial connecté uniquement
        $exhibitors = $commercial->exhibitors()
            ->withCount('badges')
            ->get();

        $exhibitorsCount = $exhibitors->count();

        $totalAuthorizedBadges = $exhibitors
            ->sum('authorized_badges');

        $totalRegisteredBadges = $exhibitors
            ->sum('badges_count');

        $remainingBadges = max(
            0,
            $totalAuthorizedBadges - $totalRegisteredBadges
        );

        // Exposants li 3emro quota kaml
        $completedExhibitors = $exhibitors
            ->filter(fn ($exhibitor) =>
                $exhibitor->authorized_badges > 0 &&
                $exhibitor->badges_count >= $exhibitor->authorized_badges
            )->count();

        // Exposants li mazal ma 3emro 7ta badge
        $pendingExhibitors = $exhibitors
            ->filter(fn ($exhibitor) =>
                $exhibitor->badges_count === 0 &&
                $exhibitor->authorized_badges > 0
            )->count();

        $partialExhibitors = $exhibitors
            ->filter(fn ($exhibitor) =>
                $exhibitor->badges_count > 0 &&
                $exhibitor->badges_count < $exhibitor->authorized_badges
            )->count();

        $badgeProgress = $totalAuthorizedBadges > 0
            ? round(
                ($totalRegisteredBadges / $totalAuthorizedBadges) * 100,
                1
            )
            : 0;

        return view('commercial.dashboard', [
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

            'exhibitorsCount' => $exhibitorsCount,
            'totalAuthorizedBadges' => $totalAuthorizedBadges,
            'totalRegisteredBadges' => $totalRegisteredBadges,
            'remainingBadges' => $remainingBadges,
            'completedExhibitors' => $completedExhibitors,
            'pendingExhibitors' => $pendingExhibitors,
            'partialExhibitors' => $partialExhibitors,
            'badgeProgress' => min(100, $badgeProgress),

            'recentExhibitors' => $commercial->exhibitors()
                ->withCount('badges')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}

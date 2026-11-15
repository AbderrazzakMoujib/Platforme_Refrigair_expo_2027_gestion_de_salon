<?php

namespace App\Http\Controllers;

use App\Models\Exhibitor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $admin = $request->user();

        // Commerciaux
        $commercials = User::query()
            ->where('role', 'commercial')
            ->orderBy('name')
            ->get();

        $commercialsCount = $commercials->count();

        $activeCommercials = $commercials
            ->where('is_active', true)
            ->count();

        // Exposants avec le nombre réel de badges
        $exhibitors = Exhibitor::query()
            ->withCount('badges')
            ->get();

        $exposantsCount = $exhibitors->count();

        $totalAuthorizedBadges = (int) $exhibitors
            ->sum('authorized_badges');

        $totalRegisteredBadges = (int) $exhibitors
            ->sum('badges_count');

        $remainingBadges = max(
            0,
            $totalAuthorizedBadges - $totalRegisteredBadges
        );

        // Exposants avec quota complet
        $completedExhibitors = $exhibitors
            ->filter(fn ($exhibitor) =>
                $exhibitor->authorized_badges > 0
                && $exhibitor->badges_count >= $exhibitor->authorized_badges
            )->count();

        // Exposants avec quelques badges
        $partialExhibitors = $exhibitors
            ->filter(fn ($exhibitor) =>
                $exhibitor->badges_count > 0
                && $exhibitor->badges_count < $exhibitor->authorized_badges
            )->count();

        // Exposants sans badges (quota > 0)
        $pendingExhibitors = $exhibitors
            ->filter(fn ($exhibitor) =>
                $exhibitor->authorized_badges > 0
                && $exhibitor->badges_count === 0
            )->count();

        $badgeProgress = $totalAuthorizedBadges > 0
            ? round(
                $totalRegisteredBadges / $totalAuthorizedBadges * 100,
                1
            )
            : 0;

        $badgeProgress = min(100, $badgeProgress);

        // Suivi par commercial
        $commercialStats = $commercials->map(function ($commercial) use ($exhibitors) {

            $clients = $exhibitors->where(
                'commercial_id',
                $commercial->id
            );

            $authorized = (int) $clients->sum('authorized_badges');
            $registered = (int) $clients->sum('badges_count');

            $complete = $clients->filter(fn ($client) =>
                $client->authorized_badges > 0
                && $client->badges_count >= $client->authorized_badges
            )->count();

            $pending = $clients->filter(fn ($client) =>
                $client->authorized_badges > 0
                && $client->badges_count === 0
            )->count();

            $progress = $authorized > 0
                ? min(100, round($registered / $authorized * 100, 1))
                : 0;

            return [
                'commercial' => $commercial,
                'exhibitors' => $clients->count(),
                'authorized' => $authorized,
                'registered' => $registered,
                'complete' => $complete,
                'pending' => $pending,
                'progress' => $progress,
            ];
        });

        // Derniers exposants créés
        $recentExhibitors = Exhibitor::query()
            ->with('commercial:id,name')
            ->withCount('badges')
            ->latest()
            ->limit(6)
            ->get();

        // Derniers comptes créés
        $recentAccounts = User::query()
            ->select(['id', 'name', 'role', 'created_at'])
            ->latest()
            ->limit(5)
            ->get();

        $adminInitials = collect(
            preg_split('/\s+/', trim($admin->name))
        )
            ->filter()
            ->take(2)
            ->map(fn (string $part) =>
                mb_strtoupper(mb_substr($part, 0, 1))
            )
            ->implode('') ?: 'A';

        return view('admin.dashboard', compact(
            'admin',
            'adminInitials',
            'commercialsCount',
            'activeCommercials',
            'exposantsCount',
            'totalAuthorizedBadges',
            'totalRegisteredBadges',
            'remainingBadges',
            'completedExhibitors',
            'partialExhibitors',
            'pendingExhibitors',
            'badgeProgress',
            'commercialStats',
            'recentExhibitors',
            'recentAccounts'
        ));
    }
}

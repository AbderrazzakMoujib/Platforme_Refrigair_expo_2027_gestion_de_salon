<?php

namespace App\Http\Controllers;

use App\Models\EquipmentRequest;
use App\Models\Exhibitor;
use App\Models\Salon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $admin = $request->user();

        /*
        |--------------------------------------------------------------------------
        | STATISTIQUES GENERALES
        |--------------------------------------------------------------------------
        */

        $exhibitors = Exhibitor::query()
            ->withCount('badges')
            ->get();

        $commercials = User::query()
            ->where('role', 'commercial')
            ->orderBy('name')
            ->get();

        $commercialsCount = $commercials->count();

        $exposantsCount = $exhibitors->count();

        $salonsCount = Salon::query()->count();

        /*
        |--------------------------------------------------------------------------
        | STATISTIQUES DES BADGES
        |--------------------------------------------------------------------------
        */

        $totalAuthorizedBadges = (int) $exhibitors
            ->sum('authorized_badges');

        $totalRegisteredBadges = (int) $exhibitors
            ->sum('badges_count');

        $remainingBadges = max(
            0,
            $totalAuthorizedBadges - $totalRegisteredBadges
        );

        $completedExhibitors = $exhibitors
            ->filter(fn ($exhibitor) =>
                (int) $exhibitor->authorized_badges > 0
                && (int) $exhibitor->badges_count
                    >= (int) $exhibitor->authorized_badges
            )
            ->count();

        $partialExhibitors = $exhibitors
            ->filter(fn ($exhibitor) =>
                (int) $exhibitor->badges_count > 0
                && (int) $exhibitor->badges_count
                    < (int) $exhibitor->authorized_badges
            )
            ->count();

        $pendingExhibitors = $exhibitors
            ->filter(fn ($exhibitor) =>
                (int) $exhibitor->authorized_badges > 0
                && (int) $exhibitor->badges_count === 0
            )
            ->count();

        $badgeProgress = $totalAuthorizedBadges > 0
            ? min(
                100,
                round(
                    $totalRegisteredBadges * 100
                    / $totalAuthorizedBadges
                )
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | STATISTIQUES DES DEMANDES D'EQUIPEMENTS
        |--------------------------------------------------------------------------
        */

        $requestsByStatus = EquipmentRequest::query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pendingRequests = (int) (
            $requestsByStatus['pending'] ?? 0
        );

        $approvedRequests = (int) (
            $requestsByStatus['approved'] ?? 0
        );

        $rejectedRequests = (int) (
            $requestsByStatus['rejected'] ?? 0
        );

        $totalRequests = $pendingRequests
            + $approvedRequests
            + $rejectedRequests;

        $requestPercentages = [
            'pending' => $totalRequests > 0
                ? round(
                    $pendingRequests * 100 / $totalRequests
                )
                : 0,

            'approved' => $totalRequests > 0
                ? round(
                    $approvedRequests * 100 / $totalRequests
                )
                : 0,

            'rejected' => $totalRequests > 0
                ? round(
                    $rejectedRequests * 100 / $totalRequests
                )
                : 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | SALONS EN COURS ET A VENIR
        |--------------------------------------------------------------------------
        */

        $upcomingSalons = Salon::query()
            ->whereIn('status', ['active', 'planned'])
            ->orderBy('start_date')
            ->limit(4)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | STATISTIQUES PAR COMMERCIAL
        |--------------------------------------------------------------------------
        */

        $commercialStats = $commercials->map(
            function ($commercial) use ($exhibitors) {

                $clients = $exhibitors->where(
                    'commercial_id',
                    $commercial->id
                );

                $authorized = (int) $clients
                    ->sum('authorized_badges');

                $registered = (int) $clients
                    ->sum('badges_count');

                $complete = $clients
                    ->filter(fn ($client) =>
                        (int) $client->authorized_badges > 0
                        && (int) $client->badges_count
                            >= (int) $client->authorized_badges
                    )
                    ->count();

                $pending = $clients
                    ->filter(fn ($client) =>
                        (int) $client->authorized_badges > 0
                        && (int) $client->badges_count === 0
                    )
                    ->count();

                $progress = $authorized > 0
                    ? min(
                        100,
                        round(
                            $registered * 100 / $authorized,
                            1
                        )
                    )
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
            }
        );

        /*
        |--------------------------------------------------------------------------
        | DERNIERS EXPOSANTS
        |--------------------------------------------------------------------------
        */

        $recentExhibitors = Exhibitor::query()
            ->with('commercial:id,name')
            ->withCount('badges')
            ->latest()
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DERNIERES DEMANDES D'EQUIPEMENTS
        |--------------------------------------------------------------------------
        */

        $recentRequests = EquipmentRequest::query()
            ->with('exhibitor:id,company_name')
            ->latest()
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ADMIN CONNECTE
        |--------------------------------------------------------------------------
        */

        $adminInitials = collect(
            preg_split('/\s+/', trim($admin->name))
        )
            ->filter()
            ->take(2)
            ->map(
                fn (string $part) =>
                    mb_strtoupper(mb_substr($part, 0, 1))
            )
            ->implode('') ?: 'A';

        /*
        |--------------------------------------------------------------------------
        | ENVOI DES DONNEES A LA VUE
        |--------------------------------------------------------------------------
        */

        return view('admin.dashboard', compact(
            'admin',
            'adminInitials',
            'salonsCount',
            'commercialsCount',
            'exposantsCount',
            'totalAuthorizedBadges',
            'totalRegisteredBadges',
            'remainingBadges',
            'completedExhibitors',
            'partialExhibitors',
            'pendingExhibitors',
            'badgeProgress',
            'pendingRequests',
            'approvedRequests',
            'rejectedRequests',
            'totalRequests',
            'requestPercentages',
            'upcomingSalons',
            'commercialStats',
            'recentExhibitors',
            'recentRequests'
        ));
    }
}

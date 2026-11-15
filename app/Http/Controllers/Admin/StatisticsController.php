<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentRequest;
use App\Models\Exhibitor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $admin = $request->user();

        $adminInitials = collect(
            preg_split('/\s+/', trim($admin->name))
        )
            ->filter()
            ->take(2)
            ->map(fn (string $part) =>
                mb_strtoupper(mb_substr($part, 0, 1))
            )
            ->implode('') ?: 'A';

        // Indicateurs généraux
        $commercialsCount = User::query()
            ->where('role', 'commercial')
            ->count();

        $activeCommercials = User::query()
            ->where('role', 'commercial')
            ->where('is_active', true)
            ->count();

        $exhibitorsCount = Exhibitor::count();

        $totalArea = (float) Exhibitor::sum('area_sqm');

        $equipmentCount = Equipment::count();

        $activeEquipment = Equipment::query()
            ->where('is_active', true)
            ->count();

        $totalAuthorizedBadges = (int) Exhibitor::sum(
            'authorized_badges'
        );

        // Demandes d'équipements
        $requestsCount = EquipmentRequest::count();

        $pendingRequests = EquipmentRequest::query()
            ->where('status', 'pending')
            ->count();

        $approvedRequests = EquipmentRequest::query()
            ->where('status', 'approved')
            ->count();

        $rejectedRequests = EquipmentRequest::query()
            ->where('status', 'rejected')
            ->count();

        $totalRequestedAmount = (float) EquipmentRequest::sum(
            'total_amount'
        );

        $approvedAmount = (float) EquipmentRequest::query()
            ->where('status', 'approved')
            ->sum('total_amount');

        $approvalRate = $requestsCount > 0
            ? round($approvedRequests / $requestsCount * 100, 1)
            : 0;

        // Évolution sur les six derniers mois calendaires.
        $start = now()->startOfMonth()->subMonths(5);
        $end = now()->endOfMonth();

        $exhibitorDates = Exhibitor::query()
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at'])
            ->countBy(fn ($item) =>
                $item->created_at->format('Y-m')
            );

        $requestDates = EquipmentRequest::query()
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at'])
            ->countBy(fn ($item) =>
                $item->created_at->format('Y-m')
            );

        $monthlyStats = collect(range(0, 5))
            ->map(function (int $offset) use (
                $start,
                $exhibitorDates,
                $requestDates
            ) {
                $month = $start->copy()->addMonths($offset);
                $key = $month->format('Y-m');

                return [
                    'label' => $month->translatedFormat('M Y'),
                    'exhibitors' => (int) (
                        $exhibitorDates[$key] ?? 0
                    ),
                    'requests' => (int) (
                        $requestDates[$key] ?? 0
                    ),
                ];
            });

        $maxMonthly = max(
            1,
            (int) $monthlyStats->max('exhibitors'),
            (int) $monthlyStats->max('requests')
        );

        // Performance des commerciaux
        $commercialStats = User::query()
            ->where('role', 'commercial')
            ->withCount('exhibitors')
            ->orderByDesc('exhibitors_count')
            ->orderBy('name')
            ->limit(10)
            ->get();

        $maxCommercialExhibitors = max(
            1,
            (int) $commercialStats->max('exhibitors_count')
        );

        // Répartition des demandes
        $requestDistribution = collect([
            [
                'label' => 'En attente',
                'count' => $pendingRequests,
                'color' => 'amber',
            ],
            [
                'label' => 'Approuvées',
                'count' => $approvedRequests,
                'color' => 'emerald',
            ],
            [
                'label' => 'Refusées',
                'count' => $rejectedRequests,
                'color' => 'red',
            ],
        ])->map(fn (array $status) => [
            ...$status,
            'percent' => $requestsCount > 0
                ? round(
                    $status['count'] / $requestsCount * 100,
                    1
                )
                : 0,
        ]);

        // Dernières demandes
        $recentRequests = EquipmentRequest::query()
            ->with('exhibitor:id,company_name')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.statistics.index', compact(
            'admin',
            'adminInitials',
            'commercialsCount',
            'activeCommercials',
            'exhibitorsCount',
            'totalArea',
            'equipmentCount',
            'activeEquipment',
            'totalAuthorizedBadges',
            'requestsCount',
            'pendingRequests',
            'approvedRequests',
            'rejectedRequests',
            'totalRequestedAmount',
            'approvedAmount',
            'approvalRate',
            'monthlyStats',
            'maxMonthly',
            'commercialStats',
            'maxCommercialExhibitors',
            'requestDistribution',
            'recentRequests'
        ));
    }
}

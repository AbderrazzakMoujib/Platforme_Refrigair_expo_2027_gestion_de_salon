<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Equipment;
use App\Models\EquipmentRequest;
use App\Models\Exhibitor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'period' => [
                'nullable',
                Rule::in(['30d', '90d', '6m', '12m', 'all']),
            ],
        ]);

        $period = $filters['period'] ?? '6m';

        $periodLabels = [
            '30d' => '30 derniers jours',
            '90d' => '90 derniers jours',
            '6m' => '6 derniers mois',
            '12m' => '12 derniers mois',
            'all' => 'Toute la période',
        ];

        $startDate = match ($period) {
            '30d' => now()->subDays(30)->startOfDay(),
            '90d' => now()->subDays(90)->startOfDay(),
            '6m' => now()->startOfMonth()->subMonths(5),
            '12m' => now()->startOfMonth()->subMonths(11),
            default => null,
        };

        // INFORMATIONS ADMIN
        $admin = $request->user();

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

        // INDICATEURS GENERAUX
        // Ces valeurs concernent tout le catalogue.
        $exhibitorsCount = Exhibitor::count();

        $commercialsCount = User::query()
            ->where('role', 'commercial')
            ->count();

        $activeCommercials = User::query()
            ->where('role', 'commercial')
            ->where('is_active', true)
            ->count();

        $totalArea = (float) Exhibitor::sum('area_sqm');

        $equipmentCount = Equipment::count();

        $activeEquipment = Equipment::query()
            ->where('is_active', true)
            ->count();

        $totalAuthorizedBadges = (int) Exhibitor::sum(
            'authorized_badges'
        );

        $registeredBadges = Badge::count();

        // DEMANDES DANS LA PERIODE SELECTIONNEE
        $requestScope = EquipmentRequest::query()
            ->when(
                $startDate,
                fn ($query) => $query->where(
                    'created_at',
                    '>=',
                    $startDate
                )
            );

        $requestsCount = (clone $requestScope)->count();

        $pendingRequests = (clone $requestScope)
            ->where('status', 'pending')
            ->count();

        $approvedRequests = (clone $requestScope)
            ->where('status', 'approved')
            ->count();

        $rejectedRequests = (clone $requestScope)
            ->where('status', 'rejected')
            ->count();

        $totalRequestedAmount = (float)
            (clone $requestScope)->sum('total_amount');

        $approvedAmount = (float)
            (clone $requestScope)
                ->where('status', 'approved')
                ->sum('total_amount');

        $approvalRate = $requestsCount > 0
            ? round(
                $approvedRequests / $requestsCount * 100,
                1
            )
            : 0;

        // REPARTITION PAR STATUT
        $requestDistribution = collect([
            [
                'key' => 'approved',
                'label' => 'Approuvées',
                'count' => $approvedRequests,
                'color' => 'emerald',
            ],
            [
                'key' => 'pending',
                'label' => 'En attente',
                'count' => $pendingRequests,
                'color' => 'amber',
            ],
            [
                'key' => 'rejected',
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

        $approvedPercent = $requestDistribution[0]['percent'];
        $pendingPercent = $requestDistribution[1]['percent'];

        // EVOLUTION DES DEMANDES
        // Graphique sur les 6 derniers mois calendaires.
        $chartStart = now()
            ->startOfMonth()
            ->subMonths(5);

        $chartDates = (clone $requestScope)
            ->where('created_at', '>=', $chartStart)
            ->get(['created_at'])
            ->countBy(
                fn ($item) => $item->created_at->format('Y-m')
            );

        $exhibitorDates = Exhibitor::query()
            ->where('created_at', '>=', $chartStart)
            ->get(['created_at'])
            ->countBy(
                fn ($item) => $item->created_at->format('Y-m')
            );

        $monthlyStats = collect(range(0, 5))
            ->map(function (int $offset) use (
                $chartStart,
                $chartDates,
                $exhibitorDates
            ) {
                $month = $chartStart
                    ->copy()
                    ->addMonths($offset);

                $key = $month->format('Y-m');

                return [
                    'label' => $month->translatedFormat('M Y'),
                    'requests' => (int) ($chartDates[$key] ?? 0),
                    'exhibitors' => (int) (
                        $exhibitorDates[$key] ?? 0
                    ),
                ];
            });

        $maxMonthly = max(
            1,
            (int) $monthlyStats->max('requests'),
            (int) $monthlyStats->max('exhibitors')
        );

        // POINTS DU GRAPHIQUE SVG
        $chartPoints = $monthlyStats
            ->values()
            ->map(function ($month, $index) use ($maxMonthly) {
                $x = 30 + ($index * 128);
                $y = 180 - (
                    ($month['requests'] / $maxMonthly) * 150
                );

                return [
                    'x' => $x,
                    'y' => round($y, 2),
                    'label' => $month['label'],
                    'value' => $month['requests'],
                ];
            });

        $chartLine = $chartPoints
            ->map(fn ($point) => $point['x'].','.$point['y'])
            ->implode(' ');

        // TOP 5 EQUIPEMENTS DEMANDES
        $equipmentItemsQuery = DB::table(
            'equipment_request_items as eri'
        )
            ->join(
                'equipment_requests as er',
                'er.id',
                '=',
                'eri.equipment_request_id'
            )
            ->leftJoin(
                'equipment as e',
                'e.id',
                '=',
                'eri.equipment_id'
            );

        if ($startDate) {
            $equipmentItemsQuery->where(
                'er.created_at',
                '>=',
                $startDate
            );
        }

        $topEquipment = (clone $equipmentItemsQuery)
            ->select(
                'eri.equipment_id',
                'eri.equipment_name',
                'e.image_path'
            )
            ->selectRaw(
                'SUM(eri.quantity) as total_quantity'
            )
            ->groupBy(
                'eri.equipment_id',
                'eri.equipment_name',
                'e.image_path'
            )
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        $maxEquipmentQuantity = max(
            1,
            (int) $topEquipment->max('total_quantity')
        );

        // CATEGORIES LES PLUS DEMANDEES
        $categoryStats = (clone $equipmentItemsQuery)
            ->selectRaw(
                "COALESCE(e.category, 'Autres') as category"
            )
            ->selectRaw(
                'SUM(eri.quantity) as total_quantity'
            )
            ->groupBy('e.category')
            ->orderByDesc('total_quantity')
            ->get();

        $totalCategoryQuantity = max(
            1,
            (int) $categoryStats->sum('total_quantity')
        );

        // TOP EXPOSANTS PAR NOMBRE DE DEMANDES
        $topExhibitors = (clone $requestScope)
            ->select('exhibitor_id')
            ->selectRaw('COUNT(*) as requests_count')
            ->whereNotNull('exhibitor_id')
            ->groupBy('exhibitor_id')
            ->with('exhibitor:id,company_name,logo')
            ->orderByDesc('requests_count')
            ->limit(5)
            ->get();

        $maxExhibitorRequests = max(
            1,
            (int) $topExhibitors->max('requests_count')
        );

        // PERFORMANCE DES COMMERCIAUX
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

        // DERNIERES DEMANDES
        $recentRequests = (clone $requestScope)
            ->with('exhibitor:id,company_name')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.statistics.index', [
            'admin' => $admin,
            'adminInitials' => $adminInitials,

            'period' => $period,
            'periodLabels' => $periodLabels,
            'periodLabel' => $periodLabels[$period],

            'exhibitorsCount' => $exhibitorsCount,
            'commercialsCount' => $commercialsCount,
            'activeCommercials' => $activeCommercials,
            'totalArea' => $totalArea,
            'equipmentCount' => $equipmentCount,
            'activeEquipment' => $activeEquipment,
            'totalAuthorizedBadges' => $totalAuthorizedBadges,
            'registeredBadges' => $registeredBadges,

            'requestsCount' => $requestsCount,
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'rejectedRequests' => $rejectedRequests,
            'totalRequestedAmount' => $totalRequestedAmount,
            'approvedAmount' => $approvedAmount,
            'approvalRate' => $approvalRate,

            'requestDistribution' => $requestDistribution,
            'approvedPercent' => $approvedPercent,
            'pendingPercent' => $pendingPercent,

            'monthlyStats' => $monthlyStats,
            'maxMonthly' => $maxMonthly,
            'chartPoints' => $chartPoints,
            'chartLine' => $chartLine,

            'topEquipment' => $topEquipment,
            'maxEquipmentQuantity' => $maxEquipmentQuantity,
            'categoryStats' => $categoryStats,
            'totalCategoryQuantity' => $totalCategoryQuantity,
            'topExhibitors' => $topExhibitors,
            'maxExhibitorRequests' => $maxExhibitorRequests,

            'commercialStats' => $commercialStats,
            'maxCommercialExhibitors' =>
                $maxCommercialExhibitors,

            'recentRequests' => $recentRequests,
        ]);
    }
}

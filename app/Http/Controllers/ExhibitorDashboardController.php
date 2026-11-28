<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\EquipmentRequest;
use App\Models\Salon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExhibitorDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user->role === UserRole::Exposant,
            403
        );

        // Fiche de l'exposant connecté uniquement.
        $exhibitor = $user->exhibitorProfile()
            ->with([
                'commercial:id,name,email,phone,profile_photo',
                'catalogProfile',
            ])
            ->first();

        // Valeurs par défaut.
        $badgeCount = 0;
        $authorizedBadges = 0;
        $remainingBadges = 0;
        $badgeProgress = 0;

        $requestsCount = 0;
        $approvedRequests = 0;
        $pendingRequests = 0;
        $recentRequests = collect();

        $catalogStatus = 'empty';
        $catalogCompleted = false;

        $reservedSlotsCount = 0;

        $activities = collect();

        if ($exhibitor) {

            /*
            |--------------------------------------------------------------------------
            | BADGES
            |--------------------------------------------------------------------------
            */

            $badgeCount = $exhibitor->badges()->count();

            $authorizedBadges = (int) $exhibitor->authorized_badges;

            $remainingBadges = max(
                0,
                $authorizedBadges - $badgeCount
            );

            $badgeProgress = $authorizedBadges > 0
                ? min(
                    100,
                    round(
                        $badgeCount * 100 / $authorizedBadges
                    )
                )
                : 0;

            /*
            |--------------------------------------------------------------------------
            | FICHE CATALOGUE
            |--------------------------------------------------------------------------
            */

            $profile = $exhibitor->catalogProfile;

            $catalogStatus = $profile?->status ?? 'empty';

            $catalogCompleted = $catalogStatus === 'completed';

            if ($profile) {
                $activities->push([
                    'type' => 'catalog',
                    'title' => $catalogCompleted
                        ? 'Fiche catalogue complétée'
                        : 'Fiche catalogue enregistrée',
                    'date' => $profile->updated_at,
                    'url' => route('exhibitor.catalog-profile.edit'),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | DEMANDES D'EQUIPEMENTS
            |--------------------------------------------------------------------------
            */

            $baseRequests = EquipmentRequest::query()
                ->where('exhibitor_id', $exhibitor->id);

            $requestsCount = (clone $baseRequests)->count();

            $approvedRequests = (clone $baseRequests)
                ->where('status', 'approved')
                ->count();

            $pendingRequests = (clone $baseRequests)
                ->where('status', 'pending')
                ->count();

            $recentRequests = (clone $baseRequests)
                ->with([
                    'items:id,equipment_request_id,equipment_id,equipment_name,quantity',
                    'items.equipment:id,image_path',
                ])
                ->latest()
                ->limit(3)
                ->get();

            foreach ($recentRequests as $equipmentRequest) {
                $activities->push([
                    'type' => 'equipment',
                    'title' => 'Demande d’équipement créée',
                    'date' => $equipmentRequest->created_at,
                    'url' => route('exhibitor.requests.index'),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | CONFERENCES & LOUNGE
            |--------------------------------------------------------------------------
            | On compte seulement les créneaux
            | réservés ou payés de cet exposant.
            */

            $reservedSlotsCount = $exhibitor->slots()
                ->whereIn('status', ['reserved', 'paid'])
                ->count();

            /*
            |--------------------------------------------------------------------------
            | ACTIVITES RECENTES
            |--------------------------------------------------------------------------
            */

            $recentBadges = $exhibitor->badges()
                ->latest()
                ->limit(3)
                ->get();

            foreach ($recentBadges as $badge) {
                $activities->push([
                    'type' => 'badge',
                    'title' => 'Badge enregistré',
                    'date' => $badge->created_at,
                    'url' => route('exhibitor.badges.index'),
                ]);
            }
        }

        $activities = $activities
            ->filter(fn ($activity) => $activity['date'] !== null)
            ->sortByDesc('date')
            ->take(5)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | PROGRESSION DE LA PREPARATION
        |--------------------------------------------------------------------------
        | Deux étapes principales :
        | - Fiche catalogue complétée
        | - Quota de badges rempli
        |
        | Les équipements et conférences sont
        | optionnels : ils ne bloquent pas les 100 %.
        */

        $dossierProgress = (int) round(
            (
                ($catalogCompleted ? 100 : 0)
                + $badgeProgress
            ) / 2
        );

        /*
        |--------------------------------------------------------------------------
        | PROCHAIN SALON
        |--------------------------------------------------------------------------
        */

        $salon = Salon::query()
            ->whereDate('end_date', '>=', today())
            ->whereNotIn('status', [
                'cancelled',
                'completed',
            ])
            ->orderBy('start_date')
            ->first();

        $daysRemaining = null;

        if ($salon?->start_date) {
            $daysRemaining = (int) max(
                0,
                ceil(
                    today()->diffInDays(
                        $salon->start_date,
                        false
                    )
                )
            );
        }

        return view('exhibitor.dashboard', [
            'user' => $user,
            'exhibitor' => $exhibitor,

            'badgeCount' => $badgeCount,
            'authorizedBadges' => $authorizedBadges,
            'remainingBadges' => $remainingBadges,
            'badgeProgress' => $badgeProgress,

            'requestsCount' => $requestsCount,
            'approvedRequests' => $approvedRequests,
            'pendingRequests' => $pendingRequests,
            'recentRequests' => $recentRequests,

            'catalogStatus' => $catalogStatus,
            'catalogCompleted' => $catalogCompleted,

            'reservedSlotsCount' => $reservedSlotsCount,

            'dossierProgress' => $dossierProgress,
            'activities' => $activities,

            'salon' => $salon,
            'daysRemaining' => $daysRemaining,
        ]);
    }
}

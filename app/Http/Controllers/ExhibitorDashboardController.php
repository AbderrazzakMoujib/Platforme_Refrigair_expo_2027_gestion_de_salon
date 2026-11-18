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

        // Seule la fiche liée au compte connecté.
        $exhibitor = $user->exhibitorProfile()
            ->with([
                'commercial:id,name,email,phone,profile_photo',
            ])
            ->first();

        $badgeCount = $exhibitor
            ? $exhibitor->badges()->count()
            : 0;

        $requestsCount = 0;
        $approvedRequests = 0;
        $pendingRequests = 0;
        $recentRequests = collect();

        if ($exhibitor) {
            $baseRequests = EquipmentRequest::query()
                ->where('exhibitor_id', $exhibitor->id);

            $requestsCount = (clone $baseRequests)
                ->count();

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
        }

        // Prochain salon programmé.
        // Aucun rattachement direct exposant/salon
        // n'existe encore dans le schéma actuel.
        $salon = Salon::query()
            ->whereDate('end_date', '>=', today())
            ->whereNotIn('status', [
                'cancelled',
                'completed',
            ])
            ->orderBy('start_date')
            ->first();

        $daysRemaining = null;

        if ($salon && $salon->start_date) {
            $daysRemaining = max(
                0,
                today()->diffInDays(
                    $salon->start_date,
                    false
                )
            );

            $daysRemaining = (int) ceil($daysRemaining);
        }

        return view('exhibitor.dashboard', [
            'user' => $user,
            'exhibitor' => $exhibitor,
            'badgeCount' => $badgeCount,

            'requestsCount' => $requestsCount,
            'approvedRequests' => $approvedRequests,
            'pendingRequests' => $pendingRequests,
            'recentRequests' => $recentRequests,

            'salon' => $salon,
            'daysRemaining' => $daysRemaining,
        ]);
    }
}

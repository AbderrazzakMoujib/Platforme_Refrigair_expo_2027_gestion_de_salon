<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user && $user->is_active, 403);

        // Routes des notifications selon le rôle.
        $prefix = match ($user->role) {
            UserRole::Admin => 'admin.notifications',

            UserRole::Commercial => 'commercial.notifications',

            UserRole::Exposant => 'exhibitor.notifications',

            UserRole::ResponsableConferences =>
                'responsable-conferences.notifications',

            default => abort(403),
        };

        // Les cinq dernières notifications
        // appartenant uniquement à l'utilisateur connecté.
        $notifications = $user->notifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($notification) use ($user, $prefix) {

                $data = $notification->data ?? [];

                $type = $data['type'] ?? 'general';

                $requestId = $data['slot_request_id'] ?? null;

                // URL par défaut : ouverture de la notification
                // et marquage comme lue.
                $openUrl = route(
                    $prefix . '.open',
                    $notification->id
                );

                // La navigation vers une conversation doit passer
                // par le contrôleur d'ouverture de notification.
                // Cela permet de marquer la notification comme lue
                // avant de rediriger vers la conversation.
                //
                // Les types suivants sont gérés :
                //
                // conference_message
                // conference_support
                // conference_support_review
                //
                // Le contrôleur d'ouverture applique ensuite
                // les règles d'accès à la réservation.
                $isConferenceCollaboration = in_array(
                    $type,
                    [
                        'conference_message',
                        'conference_support',
                        'conference_support_review',
                    ],
                    true
                );

                $hasValidRequestId = is_scalar($requestId)
                    && ctype_digit((string) $requestId)
                    && (int) $requestId > 0;

                // On conserve toujours l'URL d'ouverture
                // sécurisée de la notification.
                //
                // La redirection vers le chat est effectuée
                // par AccountNotificationController (Exposant)
                // ou ResponsableConferenceNotificationController
                // (Responsable Conférences).
                if (
                    $isConferenceCollaboration
                    && $hasValidRequestId
                    && in_array(
                        $user->role,
                        [
                            UserRole::Exposant,
                            UserRole::ResponsableConferences,
                        ],
                        true
                    )
                ) {
                    $openUrl = route(
                        $prefix . '.open',
                        $notification->id
                    );
                }

                return [
                    'id' => $notification->id,

                    'title' => $data['title']
                        ?? 'Notification',

                    'message' => $data['message']
                        ?? '',

                    'type' => $type,

                    'unread' => is_null(
                        $notification->read_at
                    ),

                    // Compatible avec les anciens composants.
                    'time' => $notification->created_at
                        ->format('d/m/Y H:i'),

                    // Utilisé par x-notifications.live.
                    'created_at' => $notification->created_at
                        ->format('d/m/Y H:i'),

                    'open_url' => $openUrl,
                ];
            })
            ->values();

        return response()->json([
            'unread_count' => $user
                ->unreadNotifications()
                ->count(),

            'notifications' => $notifications,

            'index_url' => route(
                $prefix . '.index'
            ),
        ])->header(
            'Cache-Control',
            'private, no-store'
        );
    }
}

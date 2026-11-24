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

        if ($user->role === UserRole::ResponsableConferences) {
            $notifications = $user->notifications()->latest()->limit(5)->get()->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Notification',
                'message' => $notification->data['message'] ?? '',
                'type' => $notification->data['type'] ?? 'general',
                'unread' => is_null($notification->read_at),
                'time' => $notification->created_at->format('d/m/Y H:i'),
                'open_url' => route('responsable-conferences.dashboard'),
            ]);

            return response()->json([
                'unread_count' => $user->unreadNotifications()->count(),
                'notifications' => $notifications,
                'index_url' => route('responsable-conferences.dashboard'),
            ])->header('Cache-Control', 'private, no-store');
        }

        $prefix = match ($user->role) {
            UserRole::Admin => 'admin.notifications',
            UserRole::Commercial => 'commercial.notifications',
            UserRole::Exposant => 'exhibitor.notifications',
            default => abort(403),
        };

        $notifications = $user->notifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($notification) use ($prefix) {
                return [
                    'id' => $notification->id,

                    'title' => $notification->data['title']
                        ?? 'Notification',

                    'message' => $notification->data['message']
                        ?? '',

                    'type' => $notification->data['type']
                        ?? 'general',

                    'unread' => is_null($notification->read_at),

                    'time' => $notification->created_at
                        ->format('d/m/Y H:i'),

                    'open_url' => route(
                        $prefix . '.open',
                        $notification->id
                    ),
                ];
            });

        return response()->json([
            'unread_count' => $user
                ->unreadNotifications()
                ->count(),

            'notifications' => $notifications,

            'index_url' => route($prefix . '.index'),
        ])->header('Cache-Control', 'private, no-store');
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeLookupController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_token' => ['required', 'uuid'],
        ]);

        $badge = Badge::query()
            ->with('exhibitor:id,company_name,stand_number')
            ->where('qr_token', $validated['qr_token'])
            ->first();

        if (! $badge) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'badge_not_found',
                    'message' => 'Aucun badge correspondant à ce QR Code.',
                ],
            ], 404);
        }

        if (! $badge->is_active) {
            return response()->json([
                'success' => true,
                'data' => [
                    'qr_token' => $badge->qr_token,
                    'status' => 'inactive',
                    'active' => false,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'qr_token' => $badge->qr_token,
                'status' => 'active',
                'active' => true,
                'first_name' => $badge->first_name,
                'last_name' => $badge->last_name,
                'function' => $badge->function,
                'company' => [
                    'name' => $badge->exhibitor?->company_name,
                    'stand_number' => $badge->exhibitor?->stand_number,
                ],
            ],
        ]);
    }
}

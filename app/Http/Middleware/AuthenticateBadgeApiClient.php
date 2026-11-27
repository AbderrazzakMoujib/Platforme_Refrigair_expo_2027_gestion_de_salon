<?php

namespace App\Http\Middleware;

use App\Models\BadgeApiClient;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateBadgeApiClient
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array(config('app.env'), ['local', 'testing'], true) && ! $request->isSecure()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'https_required',
                    'message' => 'Cette API exige une connexion HTTPS.',
                ],
            ], 400);
        }

        $plainTextToken = $request->bearerToken();

        if (! is_string($plainTextToken) || strlen($plainTextToken) !== 64) {
            return $this->unauthorized();
        }

        $client = BadgeApiClient::query()
            ->where('token_hash', hash('sha256', $plainTextToken))
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->first();

        if (! $client) {
            return $this->unauthorized();
        }

        $client->forceFill(['last_used_at' => now()])->saveQuietly();
        $request->attributes->set('badge_api_client_id', $client->id);

        return $next($request);
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'unauthenticated',
                'message' => 'Un jeton client valide est requis.',
            ],
        ], 401);
    }
}

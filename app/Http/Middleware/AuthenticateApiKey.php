<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?: $request->header('X-API-Key');

        if (! $token) {
            return response()->json(['message' => 'API key is required.'], 401);
        }

        $key = ApiKey::query()->where('key_hash', hash('sha256', $token))->first();

        if (! $key || ($key->expires_at && $key->expires_at->isPast())) {
            return response()->json(['message' => 'The API key is invalid or expired.'], 401);
        }

        $key->forceFill(['last_used_at' => now()])->save();
        $request->setUserResolver(fn () => $key->user);

        return $next($request);
    }
}

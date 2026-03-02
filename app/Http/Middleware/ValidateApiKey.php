<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures every request carries a valid X-API-KEY header.
 *
 * This blocks direct abuse of the public Fly.dev URL: even if someone
 * discovers the backend URL they cannot call it without the shared secret
 * that only the Vercel frontend knows.
 *
 * Excluded automatically (never reaches this middleware):
 *  - POST /api/webhooks/stripe  → registered before the protected group
 *  - GET  /api/health           → registered before the protected group
 */
final class ValidateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('app.internal_api_key');

        // If no key is configured we fail closed in production.
        if (empty($expected)) {
            if (app()->isProduction()) {
                return $this->unauthorized($request);
            }

            // In local/testing environments allow requests without a key so
            // developers do not need to configure the env var every run.
            return $next($request);
        }

        if (!hash_equals($expected, (string) $request->header('X-API-KEY', ''))) {
            return $this->unauthorized($request);
        }

        return $next($request);
    }

    private function unauthorized(Request $request): JsonResponse
    {
        return response()->json([
            'error' => [
                'code'           => 'INVALID_API_KEY',
                'message'        => 'Missing or invalid API key.',
                'correlation_id' => $request->attributes->get(CorrelationIdMiddleware::ATTRIBUTE),
            ],
        ], Response::HTTP_UNAUTHORIZED);
    }
}

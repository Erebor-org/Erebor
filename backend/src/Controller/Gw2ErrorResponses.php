<?php

namespace App\Controller;

use App\Service\Gw2\Gw2ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Turns GW2 API failures into JSON errors with a stable code, for the GW2 controllers.
 */
trait Gw2ErrorResponses
{
    // GW2 failures are never answered with 401/403: the frontend treats a 401 as an expired session
    private const ERROR_STATUS = [
        Gw2ApiException::NOT_CONFIGURED => 503,
        Gw2ApiException::INVALID_KEY => 502,
        Gw2ApiException::MISSING_PERMISSION => 502,
        Gw2ApiException::NOT_FOUND => 404,
        Gw2ApiException::RATE_LIMITED => 429,
        Gw2ApiException::UNAVAILABLE => 503,
    ];

    private function handle(callable $build): JsonResponse
    {
        try {
            return $this->json($build());
        } catch (Gw2ApiException $e) {
            return $this->json(
                ['error' => $e->getMessage(), 'code' => $e->getReason()],
                self::ERROR_STATUS[$e->getReason()] ?? 503,
            );
        }
    }
}

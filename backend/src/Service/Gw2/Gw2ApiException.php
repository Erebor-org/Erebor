<?php

namespace App\Service\Gw2;

/**
 * Every failure of the GW2 API, reduced to a small set of reasons the controller can map
 * to a stable error code. Never carries the API key.
 */
class Gw2ApiException extends \RuntimeException
{
    public const NOT_CONFIGURED = 'not_configured';
    public const INVALID_KEY = 'invalid_key';
    public const MISSING_PERMISSION = 'missing_permission';
    public const NOT_FOUND = 'not_found';
    public const RATE_LIMITED = 'rate_limited';
    public const UNAVAILABLE = 'unavailable';

    public function __construct(private string $reason, string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}

<?php

namespace App\Support\Ai;

use RuntimeException;

/**
 * A draft that could not be written, with the reason the panel shows:
 * "unavailable" (no key), "declined" (the model refused) or "failed"
 * (anything else: the network, a limit, a reply that was not a draft).
 */
class DraftFailed extends RuntimeException
{
    public function __construct(public readonly string $reason, string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message !== '' ? $message : $reason, 0, $previous);
    }
}

<?php

namespace App\Support\Ai;

/** Bound when no key is configured: the feature is absent, and says so if asked. */
class NullDescriptionDrafter implements DescriptionDrafter
{
    public function available(): bool
    {
        return false;
    }

    public function draft(array $facts): array
    {
        throw new DraftFailed('unavailable');
    }
}

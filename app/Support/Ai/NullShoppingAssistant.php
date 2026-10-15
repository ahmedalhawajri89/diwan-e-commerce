<?php

namespace App\Support\Ai;

/** Bound when no key is configured: the assistant is absent from the shop. */
class NullShoppingAssistant implements ShoppingAssistant
{
    public function available(): bool
    {
        return false;
    }

    public function reply(array $history, string $question, ?array $room, string $locale): array
    {
        throw new DraftFailed('unavailable');
    }
}

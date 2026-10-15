<?php

namespace App\Support\Ai;

/**
 * «Ask Diwan»: answers a visitor's question about what to buy, from the live
 * catalogue only, and names the pieces to show. The pieces themselves are
 * drawn by the page from the database, so no price or size the visitor sees
 * comes from the model's words.
 */
interface ShoppingAssistant
{
    public function available(): bool;

    /**
     * @param  list<array{role: 'user'|'assistant', text: string}>  $history  earlier turns, oldest first
     * @param  array{width:int,length:int}|null  $room  the room saved on the site
     * @return array{reply: string, product_ids: list<int>}
     *
     * @throws DraftFailed
     */
    public function reply(array $history, string $question, ?array $room, string $locale): array;
}

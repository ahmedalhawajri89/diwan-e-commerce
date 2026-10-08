<?php

namespace App\Support\Ai;

/**
 * Writes a first draft of a piece's description, in English and Arabic,
 * from the facts the shop has recorded about it and nothing else.
 *
 * A draft is never saved by itself: the panel puts it in the form and the
 * operator reads it, changes it and saves it.
 */
interface DescriptionDrafter
{
    /** Whether drafting can run here at all (a key is configured). */
    public function available(): bool;

    /**
     * @param  array<string, mixed>  $facts  from ProductFacts
     * @return array{en: string, ar: string, name_ar: string|null}
     *
     * @throws DraftFailed
     */
    public function draft(array $facts): array;
}

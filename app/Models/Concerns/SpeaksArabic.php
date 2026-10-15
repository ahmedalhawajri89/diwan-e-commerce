<?php

namespace App\Models\Concerns;

/**
 * Reads a model's text in the visitor's language.
 *
 * `$product->name` returns the Arabic name on the Arabic site when one is on
 * file, and the English one otherwise — so every surface that prints a name
 * (cards, search, the bag, the structured data) is bilingual without each one
 * having to remember to be.
 *
 * Forms that EDIT the record must not read through this: an operator using the
 * panel in Arabic would see the Arabic name in the English field and save it
 * there. They read `raw('name')` instead.
 */
trait SpeaksArabic
{
    /** The stored value of a translatable column, untouched by the locale. */
    public function raw(string $column): mixed
    {
        return $this->getAttributes()[$column] ?? null;
    }

    protected function inLocale(string $column, mixed $value): mixed
    {
        if (app()->getLocale() === 'ar') {
            $arabic = $this->getAttributes()[$column.'_ar'] ?? null;

            if (filled($arabic)) {
                return $arabic;
            }
        }

        return $value;
    }
}

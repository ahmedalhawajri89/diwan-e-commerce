<?php

/**
 * design.md § Nav and footer: a statement, not a sitemap. The 2026-10-04 pass
 * shipped four headed columns of links (Shop · The house · Help · Account),
 * the footer the system bans by name. One row of links, no column headings.
 */
it('closes the page with one row of links, not columns', function (string $locale) {
    $html = $this->withSession(['locale' => $locale])->get(route('furnish.index'))->getContent();

    preg_match('/<footer\b.*?<\/footer>/s', $html, $m);
    $footer = $m[0] ?? '';

    expect($footer)->not->toBe('')
        ->and(substr_count($footer, '<nav'))->toBe(1)
        ->and(substr_count($footer, '<h3'))->toBe(0)
        ->and($footer)->toContain(route('furnish.page', 'shipping'))
        ->and($footer)->toContain(route('furnish.contact'));
})->with(['en', 'ar']);

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

it('claims only what the catalogue can stand behind', function (string $locale) {
    // "Every piece measured before it is photographed" was printed on every
    // page while some pieces carry no measurements at all.
    $html = $this->get(route('furnish.index', ['locale' => $locale]))->getContent();

    expect($html)->not->toContain('Every piece measured')
        ->not->toContain('كل قطعة تُقاس')
        ->toContain(e(__('diwan.brand.tagline', [], $locale)));
})->with(['en', 'ar']);

it('makes no claim about how pieces are made that the shop has not recorded', function (string $locale) {
    // "Finished by hand", "joints fitted, surfaces oiled", "nothing ships
    // without its dimensions": none of it was on file, and the last was false.
    $html = $this->withSession(['locale' => $locale])->get(route('furnish.index'))->getContent();

    expect($html)->not->toContain('finished by hand')
        ->not->toContain('surfaces oiled')
        ->not->toContain('تُنهى يدوياً')
        ->not->toContain('أسطح مزيّتة');
})->with(['en', 'ar']);

it('lets the tape\'s label run in its own direction', function () {
    $html = $this->get(route('furnish.index', ['locale' => 'ar']))->getContent();

    expect($html)->toMatch('/<span dir="auto"[^>]*>175 سم · إنسان<\/span>/');
});

<?php

use App\Models\ProductVariant;
use App\Support\Fit;

/**
 * The fit system's arithmetic is the one piece of this project that makes a
 * claim to the customer. It has to be right, and it has to keep being right.
 */
function variant(?int $w, ?int $d, ?int $h = 80): ProductVariant
{
    return new ProductVariant([
        'width_cm' => $w,
        'depth_cm' => $d,
        'height_cm' => $h,
    ]);
}

$living = ['width' => 350, 'length' => 480];   // 16.8 m²
$small = ['width' => 200, 'length' => 250];   //  5.0 m²

it('says nothing it cannot know when dimensions are missing', function () use ($living) {
    expect(Fit::assess(variant(null, null), $living)['status'])->toBe(Fit::UNKNOWN);
    expect(Fit::assess(variant(180, null), $living)['status'])->toBe(Fit::UNKNOWN);
    expect(Fit::assess(null, $living)['status'])->toBe(Fit::UNKNOWN);
});

it('distinguishes a missing room from a missing measurement', function () {
    // Both are "we cannot tell you yet", but only one is the visitor's to fix,
    // and the UI offers a different answer for each.
    expect(Fit::assess(variant(180, 88), null)['status'])->toBe(Fit::NO_ROOM);
    expect(Fit::assess(variant(null, null), null)['status'])->toBe(Fit::UNKNOWN);
});

it('still reports a footprint when there is no room to compare against', function () {
    $result = Fit::assess(variant(200, 100), null);

    expect($result['footprint'])->toBe(2.0)
        ->and($result['share'])->toBeNull();
});

it('passes a piece that fits with a walkway left over', function () use ($living) {
    $result = Fit::assess(variant(182, 88, 75), $living);

    expect($result['status'])->toBe(Fit::FITS)
        ->and($result['note'])->toBeNull();
});

it('refuses a piece longer than the longest wall', function () use ($small) {
    $result = Fit::assess(variant(300, 90, 75), $small);

    expect($result['status'])->toBe(Fit::NO)
        ->and($result['note'])->toBe('too_long');
});

it('refuses a piece that swallows more than half the floor', function () use ($small) {
    // 178 x 213 = 3.79 m² in a 5 m² room — it goes between the walls, and it
    // is still not furniture the visitor can live with.
    $result = Fit::assess(variant(178, 213, 77), $small);

    expect($result['status'])->toBe(Fit::NO)
        ->and($result['note'])->toBe('dominates');
});

it('reports the most severe reason, not the first one checked', function () use ($small) {
    // This piece trips both the doorway rule and the crowding rule. The
    // doorway used to win because it was tested first, which buried the fact
    // the visitor actually needed.
    $result = Fit::assess(variant(172, 213, 111), $small);

    expect($result['status'])->toBe(Fit::NO)
        ->and($result['note'])->toBe('dominates');
});

it('warns about a doorway only when nothing worse is wrong', function () use ($living) {
    // 228 x 86 x 84: comfortable in a 16.8 m² room, but no face is under 80cm.
    $result = Fit::assess(variant(228, 86, 84), $living);

    expect($result['status'])->toBe(Fit::TIGHT)
        ->and($result['note'])->toBe('doorway');
});

it('gives the same piece different verdicts in different rooms', function () use ($living, $small) {
    $piece = fn () => variant(178, 213, 77);

    expect(Fit::assess($piece(), $living)['status'])->toBe(Fit::FITS)
        ->and(Fit::assess($piece(), $small)['status'])->toBe(Fit::NO);
});

it('totals a set of pieces and their share of the floor', function () use ($living) {
    $total = Fit::total([variant(200, 100), variant(100, 100), variant(null, null)], $living);

    expect($total['footprint'])->toBe(3.0)
        ->and(round($total['share'], 4))->toBe(round(3.0 / 16.8, 4))
        ->and($total['unknown'])->toBe(1);
});

it('counts unmeasured pieces rather than guessing them', function () {
    $total = Fit::total([variant(null, null), variant(null, null)], null);

    expect($total['footprint'])->toBe(0.0)
        ->and($total['unknown'])->toBe(2)
        ->and($total['share'])->toBeNull();
});

it('gives every status an icon so colour is never the only signal', function () {
    // Asserts the icon is one the app can actually draw, rather than that its
    // name starts with `bi-`. The prefix was the webfont's; the sprite has no
    // prefix, and a status whose icon is missing from the sprite renders an
    // empty <use> — which is exactly the colour-only signal this forbids.
    $sprite = require dirname(__DIR__, 2).'/config/icons.php';

    foreach ([Fit::FITS, Fit::TIGHT, Fit::NO, Fit::UNKNOWN, Fit::NO_ROOM] as $status) {
        expect(Fit::icon($status))->toBeIn($sprite);
    }
});

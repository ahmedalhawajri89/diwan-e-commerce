<?php

use App\Models\ProductVariant;
use App\Support\Passage;

/**
 * Whether a piece can get into the home is a claim made to the customer, like
 * the fit. Every case here errs the way the class promises: towards "it will
 * not go", never towards a sofa stuck in a lift.
 */

// A residential lift to ISO 4190-1 (630 kg): car 110 × 140, 220 high; door 80 × 200.
$iso = ['door_w' => 80, 'door_h' => 200, 'w' => 110, 'd' => 140, 'h' => 220];

it('reads a piece\'s three sides smallest first, and nothing when one is missing', function () {
    expect(Passage::dimensions([220, 95, 85]))->toBe([85, 95, 220])
        ->and(Passage::dimensions(new ProductVariant(['width_cm' => 180, 'depth_cm' => 40, 'height_cm' => 200])))->toBe([40, 180, 200])
        ->and(Passage::dimensions([220, null, 85]))->toBeNull()
        ->and(Passage::dimensions(new ProductVariant(['width_cm' => 180, 'depth_cm' => 40])))->toBeNull()
        ->and(Passage::dimensions(null))->toBeNull();
});

it('passes a door with room to spare, warns inside the margin, and refuses past it', function () {
    // A door 80 wide; the piece's thinnest side against it.
    expect(Passage::opening([76, 90, 200], 80, 200))->toMatchArray(['status' => Passage::PASSES, 'gap' => 4])
        ->and(Passage::opening([79, 90, 200], 80, 200))->toMatchArray(['status' => Passage::TIGHT, 'gap' => 1])
        ->and(Passage::opening([80, 90, 200], 80, 200))->toMatchArray(['status' => Passage::TIGHT, 'gap' => 0]);

    // One centimetre over: no, and it says which side against which opening.
    expect(Passage::opening([81, 90, 200], 80, 200))
        ->toMatchArray(['status' => Passage::NO, 'gap' => -1, 'need' => 81, 'room' => 80]);
});

it('turns a piece upright when it will not go through on its side', function () {
    // A cross-section of 85 × 120. Through an opening 130 wide and 100 high it
    // will not go with 120 upright, but turned (85 up, 120 across) it clears
    // both, by 15 and by 10.
    expect(Passage::opening([85, 120, 200], 130, 100))->toMatchArray(['status' => Passage::PASSES, 'gap' => 10]);

    // 90 wide and 110 high: neither way round.
    expect(Passage::opening([85, 120, 200], 90, 110)['status'])->toBe(Passage::NO);
});

it('fits a long piece standing up in a standard lift, and refuses one too long for any way', function () use ($iso) {
    // A wardrobe side 200 long: it stands in a car 220 high with 20 to spare,
    // and leans along the depth with more (√(140² + 220²) − 25 ≈ 235). The
    // roomier way is the one reported.
    expect(Passage::lift([25, 75, 200], $iso))->toMatchArray(['status' => Passage::PASSES, 'where' => 'lift_car', 'gap' => 35]);

    // 260 long: longer than the car is high, and than any diagonal less its thickness.
    expect(Passage::lift([25, 75, 260], $iso))->toMatchArray(['status' => Passage::NO, 'where' => 'lift_car']);
});

it('stops a deep sofa at the lift\'s door before it reaches the car', function () use ($iso) {
    expect(Passage::lift([85, 95, 220], $iso))
        ->toMatchArray(['status' => Passage::NO, 'where' => 'lift_door', 'need' => 85, 'room' => 80]);
});

it('leans a piece across the car when nothing straighter works', function () use ($iso) {
    // 230 long and thin: too long to stand (220) or lie (140), but it leans
    // from a floor corner to the ceiling along the depth: √(140² + 220²) − 20 ≈ 240.
    expect(Passage::lift([20, 60, 230], $iso))->toMatchArray(['status' => Passage::PASSES, 'where' => 'lift_car']);
});

it('carries a piece round a turn by the corner rule, and its thickness costs length', function () {
    // A rod (no thickness) between two 100 cm passages: the longest is 2√2 × 100 ≈ 282.8,
    // rounded down, never up.
    expect(Passage::corner([0, 0, 1000], 100, 100)['room'])->toBe(282);

    // 30 cm thick: 282.8 − 2 × 30 ≈ 222.
    expect(Passage::corner([30, 90, 210], 100, 100))->toMatchArray(['status' => Passage::PASSES, 'room' => 222])
        ->and(Passage::corner([30, 90, 225], 100, 100))->toMatchArray(['status' => Passage::NO, 'need' => 225, 'room' => 222]);

    // As thick as the narrower passage: it does not even start round, short
    // as it is. A gap of nought is no here, not tight.
    expect(Passage::corner([100, 120, 150], 100, 120))->toMatchArray(['status' => Passage::NO, 'need' => 100, 'room' => 100])
        ->and(Passage::corner([105, 120, 150], 100, 120))->toMatchArray(['status' => Passage::NO, 'gap' => -5]);
});

it('says nothing it cannot know', function () {
    $sofa = [85, 95, 200];

    expect(Passage::assess([85, null, 200], ['door' => ['w' => 90, 'h' => 200]]))->toMatchArray(['status' => Passage::UNKNOWN, 'where' => 'piece'])
        ->and(Passage::assess($sofa, null)['status'])->toBe(Passage::UNKNOWN)
        ->and(Passage::assess($sofa, ['door' => ['w' => null, 'h' => null]]))->toMatchArray(['status' => Passage::UNKNOWN, 'unchecked' => ['door']]);
});

it('never says it passes while a step on the way is unchecked', function () {
    // Through the door, but the lift's measurements are not known: unknown, not passes.
    $result = Passage::assess([40, 60, 120], ['door' => ['w' => 90, 'h' => 205], 'lift' => ['door_w' => 80, 'door_h' => null, 'w' => 110, 'd' => 140, 'h' => 220]]);

    expect($result['status'])->toBe(Passage::UNKNOWN)
        ->and($result['unchecked'])->toBe(['lift'])
        ->and($result['steps']['door']['status'])->toBe(Passage::PASSES);

    // An unchecked step does not soften a step that already failed.
    expect(Passage::assess([95, 100, 200], ['door' => ['w' => 90, 'h' => 205], 'stairs' => ['w' => null, 'landing' => 100]])['status'])
        ->toBe(Passage::NO);
});

it('lets the worst step decide, and names it', function () use ($iso) {
    // The front door is wide; the lift's door is not.
    $result = Passage::assess([85, 95, 200], ['door' => ['w' => 100, 'h' => 210], 'lift' => $iso]);

    expect($result)->toMatchArray(['status' => Passage::NO, 'where' => 'lift_door', 'need' => 85, 'room' => 80])
        ->and($result['steps']['door']['status'])->toBe(Passage::PASSES);
});

it('takes the stairs when there is no lift, and passes a piece that clears every step', function () {
    $route = ['door' => ['w' => 90, 'h' => 205], 'lift' => null, 'stairs' => ['w' => 100, 'landing' => 110]];

    expect(Passage::assess([40, 80, 180], $route))->toMatchArray(['status' => Passage::PASSES, 'unchecked' => []])
        ->and(array_keys(Passage::assess([40, 80, 180], $route)['steps']))->toBe(['door', 'stairs']);

    // A ground-floor home: the door is the whole way in.
    expect(Passage::assess([40, 80, 180], ['door' => ['w' => 90, 'h' => 205]])['status'])->toBe(Passage::PASSES);
});

it('checks a piece from the shop the same way, straight from its variant', function () use ($iso) {
    $bookcase = new ProductVariant(['width_cm' => 80, 'depth_cm' => 30, 'height_cm' => 200]);

    expect(Passage::assess($bookcase, ['door' => ['w' => 85, 'h' => 200], 'lift' => $iso])['status'])->toBe(Passage::PASSES);
});

it('matches pieces measured in a real home', function () {
    // To be filled with the owner's own measurements: a piece that is known to
    // have gone in, and the door and lift or stairs it went through.
})->todo();

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

it('offers the balcony when the lift will not take it, as a hoist, not a no', function () use ($iso) {
    $sofa = [85, 95, 220];   // stopped by the lift's 80 cm door
    $route = ['door' => ['w' => 95, 'h' => 210], 'lift' => $iso, 'balcony' => ['w' => 120, 'h' => 210]];

    $result = Passage::assess($sofa, $route);

    expect($result)->toMatchArray(['status' => Passage::HOIST, 'where' => 'lift_door'])
        ->and($result['steps']['balcony']['status'])->toBe(Passage::PASSES);

    // A balcony it does not clear either: still no.
    expect(Passage::assess($sofa, ['balcony' => ['w' => 80, 'h' => 200]] + $route)['status'])->toBe(Passage::NO);

    // A balcony whose size is not known: no, with the balcony left unchecked.
    expect(Passage::assess($sofa, ['balcony' => ['w' => null, 'h' => null]] + $route))
        ->toMatchArray(['status' => Passage::NO, 'unchecked' => ['balcony']]);
});

it('does not offer the balcony when the narrowest door is what stops it', function () use ($iso) {
    // That door may be the room's own, between the balcony and the room.
    expect(Passage::assess([85, 95, 220], ['door' => ['w' => 80, 'h' => 200], 'lift' => $iso, 'balcony' => ['w' => 150, 'h' => 220]]))
        ->toMatchArray(['status' => Passage::NO, 'where' => 'door']);
});

it('walks a piece in at the size it is carried, when the shop knows it', function () use ($iso) {
    $route = ['door' => ['w' => 90, 'h' => 205], 'lift' => $iso];
    $bed = fn (array $more = []) => (new ProductVariant)->forceFill($more + [
        'width_cm' => 170, 'depth_cm' => 210, 'height_cm' => 110,     // as it stands in the room
        'carry_w_cm' => 40, 'carry_d_cm' => 200, 'carry_h_cm' => 15,  // its largest box
    ]);

    // In boxes: the largest box is what goes through.
    expect(Passage::assess($bed(['arrives' => 'flat']), $route))->toMatchArray(['status' => Passage::PASSES, 'carried' => true]);

    // Arrives assembled: walked in whole, and a no is a no.
    expect(Passage::assess($bed(['arrives' => 'assembled']), $route))->toMatchArray(['status' => Passage::NO, 'note' => null]);

    // Not said: walked in whole, and it may go in apart, so not a no.
    expect(Passage::assess($bed(['arrives' => null]), $route))->toMatchArray(['status' => Passage::UNKNOWN, 'note' => 'may_disassemble']);

    // Comes apart, but the size of its largest part is not on file: the same caution.
    expect(Passage::assess($bed(['arrives' => 'parts', 'carry_h_cm' => null]), $route))
        ->toMatchArray(['status' => Passage::UNKNOWN, 'note' => 'may_disassemble', 'carried' => false]);
});

it('keeps a whole piece\'s pass when it says nothing of how it arrives', function () use ($iso) {
    $bookcase = new ProductVariant(['width_cm' => 80, 'depth_cm' => 30, 'height_cm' => 200]);

    expect(Passage::assess($bookcase, ['door' => ['w' => 85, 'h' => 200], 'lift' => $iso]))
        ->toMatchArray(['status' => Passage::PASSES, 'note' => null]);
});

it('checks a villa’s own stairs after the front door, and names them when they stop a piece', function () {
    // A wardrobe side 200 long and 60 deep: through a wide front door, then
    // up inner stairs 85 wide with an 85 landing (corner limit about 180).
    $route = ['door' => ['w' => 100, 'h' => 220], 'inner' => ['w' => 85, 'landing' => 85]];

    $result = Passage::assess([60, 90, 200], $route);
    expect($result)->toMatchArray(['status' => Passage::NO, 'where' => 'inner'])
        ->and($result['steps']['door']['status'])->toBe(Passage::PASSES);

    // A wider landing takes it: 100 wide turning into 140 allows about 216.
    expect(Passage::assess([60, 90, 200], ['inner' => ['w' => 100, 'landing' => 140]] + $route)['status'])->toBe(Passage::PASSES);

    // An upstairs balcony that clears it: lifted in instead.
    expect(Passage::assess([60, 90, 200], ['balcony' => ['w' => 120, 'h' => 210]] + $route)['status'])->toBe(Passage::HOIST);

    // Said to be there, but not measured: never a pass.
    expect(Passage::assess([40, 60, 120], ['door' => ['w' => 100, 'h' => 220], 'inner' => ['w' => null, 'landing' => null]]))
        ->toMatchArray(['status' => Passage::UNKNOWN, 'unchecked' => ['inner']]);
});

it('matches pieces measured in a real home', function () {
    // To be filled with the owner's own measurements: a piece that is known to
    // have gone in, and the door and lift or stairs it went through.
})->todo();

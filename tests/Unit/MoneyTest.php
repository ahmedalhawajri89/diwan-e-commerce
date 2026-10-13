<?php

use App\Support\Money;
use Tests\TestCase;

/**
 * Every amount in riyals, written the way a Saudi shopper reads it: the unit
 * after the figure in Arabic, the code before it in English, halalas only
 * when there are any, and figure and unit never split across a line.
 */
uses(TestCase::class);

it('writes riyals the way each language does', function () {
    expect(Money::format(1499, 'ar'))->toBe("1,499\u{00A0}ر.س")
        ->and(Money::format(1499, 'en'))->toBe("SAR\u{00A0}1,499");
});

it('shows halalas only when there are any', function () {
    expect(Money::figure(1499.00))->toBe('1,499')
        ->and(Money::figure('1499.5'))->toBe('1,499.50')
        ->and(Money::figure(0.25))->toBe('0.25')
        ->and(Money::figure(null))->toBe('0');
});

it('rounds to the halala, never to a figure the till did not hold', function () {
    expect(Money::figure(1499.999))->toBe('1,500')
        ->and(Money::figure(10.005))->toBe('10.01');
});

it('knows which side the unit goes', function () {
    expect(Money::symbolFirst('en'))->toBeTrue()
        ->and(Money::symbolFirst('ar'))->toBeFalse()
        ->and(Money::symbol('ar'))->toBe('ر.س');
});

it('formats in the page\'s language when none is given', function () {
    app()->setLocale('ar');
    expect(Money::format(250))->toBe("250\u{00A0}ر.س");
});

it('leaves no template or script gluing a currency symbol on by hand', function () {
    $root = dirname(__DIR__, 2);
    $offenders = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/resources/views')) as $file) {
        if (str_ends_with($file, '.blade.php') && str_contains(file_get_contents($file), "common.currency')")) {
            $offenders[] = str_replace($root.DIRECTORY_SEPARATOR, '', (string) $file);
        }
    }
    foreach (glob($root.'/resources/js/**/*.js') as $file) {
        if (str_contains(file_get_contents($file), "t('currency')")) {
            $offenders[] = $file;
        }
    }

    // The app-data bridge hands the unit to scripts; it is the one place allowed to.
    $offenders = array_values(array_filter($offenders, fn ($f) => ! str_contains($f, 'app-data.blade.php')));

    expect($offenders)->toBe([], 'Use @money(...) or App\Support\Money, never the symbol plus number_format().');
});

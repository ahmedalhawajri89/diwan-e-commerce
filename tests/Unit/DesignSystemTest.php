<?php

/**
 * Guards on the design system, so its rules are enforced rather than merely
 * written down. A rule nobody checks is a rule that drifts back.
 *
 * These are Unit tests on purpose: they read source files and need no database
 * and no request, so they run in milliseconds and can be the first thing CI
 * does. See design.md § The helper layer and § Theme.
 */

/** Every Blade view in the project, keyed by its path for readable failures. */
function bladeViews(): array
{
    $root = dirname(__DIR__, 2);
    $views = [];

    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root.'/resources/views', FilesystemIterator::SKIP_DOTS)
    );

    foreach ($it as $file) {
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));

        // Mail templates are exempt and always will be: an HTML email client
        // does not reliably apply a stylesheet, so inline CSS is the medium.
        //
        // Matched on `emails/` anywhere rather than on one directory. The
        // shared mail shell has to live under components/ for Blade to resolve
        // `<x-emails.layout>`, and the first version of this check keyed on
        // `resources/views/emails/` — so moving the file there made two guards
        // fail on markup that had not changed. The exemption follows what a
        // file is, not where the component resolver happens to want it.
        if (str_contains($rel, 'emails/')) {
            continue;
        }

        $views[$rel] = file_get_contents($file->getPathname());
    }

    return $views;
}

/**
 * The six style attributes the system permits, each carrying a value a
 * stylesheet cannot know. Adding to this list is a design decision and should
 * be argued for in design.md, not slipped in to make a test pass.
 */
const ALLOWED_INLINE_STYLE = [
    'resources/views/components/scale-figure.blade.php' => 2,  // drawn geometry
    'resources/views/furnish/products.blade.php' => 1,  // a swatch's real colour
];

it('keeps inline style attributes to the values a stylesheet cannot know', function () {
    $offenders = [];

    foreach (bladeViews() as $path => $source) {
        $count = preg_match_all('/\sstyle="/', $source);
        $allowed = ALLOWED_INLINE_STYLE[$path] ?? 0;

        if ($count > $allowed) {
            $offenders[$path] = "{$count} found, {$allowed} allowed";
        }
    }

    expect($offenders)->toBe(
        [],
        "Inline style attributes carry decoration that belongs in a class.\n"
        ."A `style` attribute cannot be covered by a CSP nonce or hash, so each\n"
        ."one forces `style-src-attr 'unsafe-inline'` on the whole app.\n"
        .'Use Tailwind utilities, or name the pattern as a component.'
    );
});

it('lets every inline style that remains carry a computed value', function () {
    $static = [];

    foreach (array_keys(ALLOWED_INLINE_STYLE) as $path) {
        $source = file_get_contents(dirname(__DIR__, 2).'/'.$path);

        preg_match_all('/\sstyle="([^"]*)"/', $source, $matches);

        foreach ($matches[1] as $style) {
            if (! str_contains($style, '{{')) {
                $static[] = "{$path}: {$style}";
            }
        }
    }

    expect($static)->toBe(
        [],
        "These files hold an allowance for a style attribute that carries a\n"
        ."computed value, but the attribute below is static — so it belongs\n"
        .'in a class, and the allowance should shrink.'
    );
});

it('names no colour that is not a token', function () {
    $offenders = [];

    foreach (bladeViews() as $path => $source) {
        // The 500/503 page inlines its own colours and type on purpose: it
        // must render when Vite, the database or the session store is the
        // thing that broke, so it cannot reference the built stylesheet.
        // The duplication is argued for in the file's own header comment.
        if ($path === 'resources/views/errors/minimal.blade.php') {
            continue;
        }

        // Strip Blade comments first: design.md quotes hex values in prose,
        // and the views inherited that habit in their header comments.
        $stripped = preg_replace('/\{\{--.*?--\}\}/s', '', $source);

        if (preg_match_all('/#[0-9a-fA-F]{3,8}\b|\boklch\(|\brgba?\(/', $stripped, $m)) {
            $offenders[$path] = $m[0];
        }
    }

    expect($offenders)->toBe(
        [],
        'Every colour in a view must reference a token from resources/css/app.css.'
    );
});

/** The declared icon set, read from disk so this stays a bootstrap-free test. */
function declaredIcons(): array
{
    return require dirname(__DIR__, 2).'/config/icons.php';
}

/** The symbol ids the committed sprite actually carries. */
function spriteIcons(): array
{
    $sprite = file_get_contents(
        dirname(__DIR__, 2).'/resources/views/partials/icon-sprite.blade.php'
    );

    preg_match_all('/<symbol id="i-([a-z0-9-]+)"/', $sprite, $m);

    return $m[1];
}

it('draws every icon a view names', function () {
    $declared = declaredIcons();
    $unknown = [];

    foreach (bladeViews() as $path => $source) {
        preg_match_all('/<x-icon[^>]*\bname="([a-z0-9-]+)"/', $source, $m);

        foreach (array_unique($m[1]) as $name) {
            if (! in_array($name, $declared, true)) {
                $unknown[] = "{$path}: {$name}";
            }
        }
    }

    expect($unknown)->toBe(
        [],
        'An icon not in config/icons.php is not in the sprite, and <use> on a
'
        .'missing symbol renders nothing at all — no error, just a gap.
'
        .'Add it to config/icons.php and run `php artisan icons:build`.'
    );
});

it('keeps the icon sprite in step with the declared set', function () {
    // The sprite is generated and committed, so it can fall behind its source
    // the way any generated file can. Compared as sets rather than by file
    // hash: a whitespace change in the generator is not a stale sprite.
    $declared = declaredIcons();
    $shipped = spriteIcons();
    sort($declared);
    sort($shipped);

    expect($shipped)->toBe(
        $declared,
        'The sprite and config/icons.php disagree. Run `php artisan icons:build` '
        .'and commit the result.'
    );
});

it('declares no icon nothing uses', function () {
    // The whole point of the sprite is that it carries only what is drawn. A
    // set that only ever grows is the webfont again, just smaller.
    $sources = '';
    foreach (bladeViews() as $source) {
        $sources .= $source;
    }
    // Every JavaScript module, not a hand-listed pair. The list was two files
    // until the storefront's behaviour moved out of the Blade layouts into
    // resources/js — and then `bag-plus` and `x`, drawn by wishlist.js, looked
    // unused. A guard whose scope has to be edited whenever code moves is a
    // guard that will one day be edited to make itself pass.
    $sources .= implode('', array_map(
        'file_get_contents',
        glob(dirname(__DIR__, 2).'/resources/js/furnish/*.js')
    ));

    $sources .= file_get_contents(dirname(__DIR__, 2).'/app/Support/Fit.php');

    $unused = array_values(array_filter(
        declaredIcons(),
        fn ($name) => ! str_contains($sources, "'{$name}'") && ! str_contains($sources, "\"{$name}\"")
    ));

    expect($unused)->toBe([], 'Declared but never drawn. Remove from config/icons.php.');
});

it('ships no icon webfont', function () {
    $root = dirname(__DIR__, 2).'/resources/css/';
    $css = file_get_contents($root.'app.css').file_get_contents($root.'fonts.css');

    expect(str_contains($css, 'bootstrap-icons'))->toBeFalse(
        'The webfont was 98 KB of CSS and a 131 KB woff2 to draw about thirty '
        .'glyphs. Icons come from the sprite now.'
    );
});

it('runs no database query from a template', function () {
    $offenders = [];

    foreach (bladeViews() as $path => $source) {
        // Blade comments carry `@param \App\Models\Product $product` docblocks
        // on the components; a type hint is not a query.
        $stripped = preg_replace('/\{\{--.*?--\}\}/s', '', $source);

        preg_match_all(
            '/\\\\App\\\\Models\\\\\w+::\w+|\bDB::\w+|->(?:where|whereIn|whereNull|latest|orderBy)\(/',
            $stripped,
            $m
        );

        if ($m[0]) {
            $offenders[$path] = array_values(array_unique($m[0]));
        }
    }

    expect($offenders)->toBe(
        [],
        "A template that queries is a template no controller can see, no test\n"
        ."can arrange, and no cache can wrap. Two layouts were running ten\n"
        ."queries between them on every page this way.\n"
        .'Move it to the controller, or to a composer in App\View\Composers.'
    );
});

it('carries no inline event handler', function () {
    $offenders = [];

    foreach (bladeViews() as $path => $source) {
        if (preg_match_all('/\son[a-z]+\s*=\s*"/i', $source, $m)) {
            $offenders[$path] = count($m[0]);
        }
    }

    expect($offenders)->toBe(
        [],
        "Inline event handlers force `script-src 'unsafe-inline'`, which\n"
        ."switches off the one directive that stops an injected <script>\n"
        ."from running.\n"
        .'Delegate from document in resources/js/furnish/interactions.js.'
    );
});

it('names Alpine components rather than writing them inline', function () {
    $offenders = [];

    foreach (bladeViews() as $path => $source) {
        // `x-data="dropdown"` is fine; `x-data="{ open: false }"` is a
        // component written into the markup, out of reach of a linter.
        if (preg_match_all('/\bx-data="(?![A-Za-z_]\w*")[^"]*"/', $source, $m)) {
            $offenders[$path] = $m[0];
        }
    }

    expect($offenders)->toBe(
        [],
        'Register the component with Alpine.data() in resources/js/app.js '
        .'and name it in the template.'
    );
});

it('keeps Bootstrap behaviour and the old stylesheet\'s classes out of every view', function () {
    $offenders = [];

    foreach (bladeViews() as $path => $source) {
        // The standalone 500/503 page carries its own inline styles on purpose.
        if ($path === 'resources/views/errors/minimal.blade.php') {
            continue;
        }

        $stripped = preg_replace('/\{\{--.*?--\}\}/s', '', $source);

        $found = [];

        if (preg_match_all('/\bdata-bs-[a-z-]+/', $stripped, $m)) {
            $found = array_merge($found, $m[0]);
        }

        // A class attribute naming an hm-* class: the old SCSS layer.
        if (preg_match_all('/class="[^"]*(?<![\w:-])(hm-[a-z0-9_-]+)/', $stripped, $m)) {
            $found = array_merge($found, $m[1]);
        }

        if ($found) {
            $offenders[$path] = array_values(array_unique($found));
        }
    }

    expect($offenders)->toBe([], 'Use Tailwind utilities, the ui/admin components, <dialog> with data-open-dialog, or an Alpine component.');
});

/*
 |--------------------------------------------------------------------------
 | Photographs
 |--------------------------------------------------------------------------
 */

/**
 * Views allowed a plain <img>, with a count and a reason. None today.
 */
const PLAIN_IMG_ALLOWED = [
    // The bag drawer's thumbnail: its src is set by a script from the server's
    // answer, which sends the smallest derived file (Responsive::smallest).
    'resources/views/layouts/furnish.blade.php' => 1,
    // Empty since the product gallery became a scroll-snap track of <picture>s.
    // It used to swap one <img>'s src, which a <source> set would override, so
    // the page's largest photograph was the one image phones got at full size.
];

it('serves every photograph through the responsive component', function () {
    $offenders = [];

    foreach (bladeViews() as $path => $source) {
        if (str_ends_with($path, 'components/picture.blade.php')) {
            continue;   // the component itself emits the <img>
        }

        // Strip Blade comments: the product page explains in prose why its
        // gallery keeps a plain <img>, and that sentence is not an element.
        $stripped = preg_replace('/\{\{--.*?--\}\}/s', '', $source);

        $count = preg_match_all('/<img\b/', $stripped);
        $allowed = PLAIN_IMG_ALLOWED[$path] ?? 0;

        if ($count > $allowed) {
            $offenders[$path] = "{$count} plain <img>, {$allowed} allowed";
        }
    }

    expect($offenders)->toBe(
        [],
        'A plain <img> sends one file, at one size, in one format, to every
'
        .'device. Use <x-picture>, which adds AVIF and WebP sources and a
'
        .'srcset. If this one genuinely cannot, add it to PLAIN_IMG_ALLOWED.'
    );
});

it('tells every picture what width it will be drawn at', function () {
    $missing = [];

    foreach (bladeViews() as $path => $source) {
        preg_match_all('/<x-picture\b(?:"[^"]*"|\'[^\']*\'|[^>"\'])*\/?>/s', $source, $tags);

        foreach ($tags[0] as $tag) {
            if (! str_contains($tag, 'sizes=')) {
                $missing[] = $path.': '.preg_replace('/\s+/', ' ', substr($tag, 0, 70));
            }
        }
    }

    expect($missing)->toBe(
        [],
        'Without `sizes` a browser assumes the picture fills the viewport and
'
        .'picks the largest file in the srcset — which is the exact bug the
'
        .'component exists to fix. Work out the width from the layout.'
    );
});

it('derives the images the views ask for', function () {
    $manifest = dirname(__DIR__, 2).'/public/assets/derived-manifest.json';

    if (! is_file($manifest)) {
        // Generated like public/build, and gitignored the same way, so a
        // clean checkout has neither. `images:derive --check` is what CI
        // uses; this only guards against a manifest that has gone stale.
        test()->markTestSkipped('No manifest. Run `php artisan images:derive`.');
    }

    $entries = json_decode(file_get_contents($manifest), true);
    $broken = [];

    foreach ($entries as $key => $entry) {
        foreach ($entry['formats'] ?? [] as $format => $widths) {
            foreach ($widths as $width) {
                $stem = pathinfo($key, PATHINFO_FILENAME);
                $dir = dirname($key);
                $file = dirname(__DIR__, 2)."/public/{$dir}/_derived/{$stem}-{$width}.{$format}";

                if (! is_file($file)) {
                    $broken[] = "{$key} -> {$stem}-{$width}.{$format}";
                }
            }
        }
    }

    expect($broken)->toBe(
        [],
        'The manifest promises derivatives that are not on disk. A <picture> '
        .'whose <source> 404s shows nothing at all in some browsers. '
        .'Run `php artisan images:derive --force`.'
    );
});

it('rebuilds a derivative when its photograph changes', function () {
    // The command used to ask only "does this file exist?", so replacing a
    // photograph and re-running it reported "already present" and kept serving
    // the old picture at every width. The source JPEG was new; every AVIF and
    // WebP a browser actually fetches was not.
    //
    // Caught by replacing the stool photograph and watching `images:derive`
    // write nothing. This checks the property that fix restored.
    $root = dirname(__DIR__, 2);
    $manifest = $root.'/public/assets/derived-manifest.json';

    if (! is_file($manifest)) {
        test()->markTestSkipped('No manifest. Run `php artisan images:derive`.');
    }

    $stale = [];

    foreach (json_decode(file_get_contents($manifest), true) as $key => $entry) {
        $source = $root.'/public/'.$key;

        if (! is_file($source)) {
            continue;
        }

        $stem = pathinfo($key, PATHINFO_FILENAME);
        $dir = dirname($key);

        foreach ($entry['formats'] ?? [] as $format => $widths) {
            foreach ($widths as $width) {
                $file = $root."/public/{$dir}/_derived/{$stem}-{$width}.{$format}";

                if (is_file($file) && filemtime($file) < filemtime($source)) {
                    $stale[] = "{$stem}-{$width}.{$format}";
                }
            }
        }
    }

    expect($stale)->toBe(
        [],
        'These derivatives are older than the photograph they were made from, '
        .'so the site is serving the previous picture. '
        .'Run `php artisan images:derive`.'
    );
});

it('keeps executable JavaScript out of the templates', function () {
    // 480 lines of search, wishlist, menu and admin logic lived in <script>
    // blocks across three Blade files. Signing them with a CSP nonce made them
    // safe; it did not make them lintable, testable, or visible to anything
    // that reads JavaScript. They are modules under resources/js/furnish now.
    //
    // Blade comments are stripped first. Several of those files explain in
    // prose where their <script> went, and an earlier version of this count
    // read those sentences as code.
    $offenders = [];

    foreach (bladeViews() as $path => $source) {
        $stripped = preg_replace('/\{\{--.*?--\}\}/s', '', $source);

        preg_match_all('/<script([^>]*)>(.*?)<\/script>/s', $stripped, $matches, PREG_SET_ORDER);

        foreach ($matches as [, $attributes, $body]) {
            // A data block is not executable, and neither is an external file.
            // Speculation rules are JSON the browser reads, not code it runs.
            if (str_contains($attributes, 'application/json')
                || str_contains($attributes, 'application/ld+json')
                || str_contains($attributes, 'type="speculationrules"')
                || str_contains($attributes, 'src=')) {
                continue;
            }

            if (trim($body) !== '') {
                $offenders[$path] = count(preg_split('/\R/', trim($body))).' lines';
            }
        }
    }

    expect($offenders)->toBe(
        [],
        'A template is the wrong place for behaviour: no linter reads it, no
'
        .'test can exercise it, and no bundler can split or minify it.
'
        .'Add a module under resources/js/furnish and start it from main.js.'
    );
});

it('is built on Tailwind alone', function () {
    $root = dirname(__DIR__, 2);

    // No stylesheet language beside Tailwind: no .scss anywhere in resources/.
    $scss = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/resources', FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (str_ends_with($file->getFilename(), '.scss')) {
            $scss[] = $file->getPathname();
        }
    }
    expect($scss)->toBe([], 'SCSS has no place in this project; write Tailwind utilities.');

    // No Bootstrap package. bootstrap-icons stays: BuildIconSprite reads its SVGs.
    $package = json_decode(file_get_contents($root.'/package.json'), true);
    $deps = array_keys(($package['dependencies'] ?? []) + ($package['devDependencies'] ?? []));
    expect(array_values(array_intersect($deps, ['bootstrap', 'sass'])))->toBe([]);

    // The migration prefix is gone for good.
    $prefixed = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/resources', FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (preg_match('/\.(php|js|css)$/', $file->getFilename())
            && preg_match('/(?<![\w-])tw:/', file_get_contents($file->getPathname()))) {
            $prefixed[] = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        }
    }
    expect($prefixed)->toBe([], 'The tw: prefix belonged to the move off Bootstrap; utilities are unprefixed now.');
});

it('names what it transitions', function () {
    // `transition-all` animates whatever happens to change, layout included:
    // the footer's link rule grew by animating `width` and `margin`, which
    // reflows the line on every frame. design.md § Motion bans both.
    $offenders = [];

    foreach (bladeViews() as $path => $source) {
        if (preg_match_all('/\btransition-all\b|transition:\s*all\b/', $source, $m)) {
            $offenders[$path] = count($m[0]);
        }
    }

    expect($offenders)->toBe([], 'Name the properties: transition-colors, transition-transform, transition-opacity.');
});

it('generates a sprite that obeys the same rules as the views', function () {
    // The committed sprite was fixed by hand when the hm- classes went, and
    // the generator was not: the next `icons:build` put `hm-visually-hidden`
    // back, a class with no rule, so the sprite drew at full size on the page.
    $generator = file_get_contents(dirname(__DIR__, 2).'/app/Console/Commands/BuildIconSprite.php');

    expect($generator)->not->toMatch('/class="[^"]*\bhm-/');
});

it('submits no form on change', function () {
    // Filters, the sort, bag quantities and the room picker each submitted on
    // `change` (AUDIT C1): an arrow key on a select or a number reloaded the
    // page, and a keyboard user could not get past the first option.
    $offenders = [];

    foreach (bladeViews() as $path => $source) {
        if (str_contains($source, 'data-autosubmit') || preg_match('/onchange=|@change="\$?el\.form|form\.submit\(\)/', $source)) {
            $offenders[] = $path;
        }
    }

    $js = implode('', array_map('file_get_contents', glob(dirname(__DIR__, 2).'/resources/js/**/*.js')));

    expect($offenders)->toBe([])
        ->and($js)->not->toContain('form.submit()');
});

it('writes figures one way in Arabic: the site\'s Western digits', function () {
    // Eight Arabic strings used Arabic-Indic digits beside Western ones from
    // the database, so one drawing read "١٧٥ سم" next to "120 × 75" (D7).
    $offenders = [];

    foreach (glob(dirname(__DIR__, 2).'/lang/ar/*.php') as $file) {
        foreach (file($file) as $n => $line) {
            if (preg_match('/[\x{0660}-\x{0669}\x{06F0}-\x{06F9}]/u', $line)) {
                $offenders[] = basename($file).':'.($n + 1);
            }
        }
    }

    expect($offenders)->toBe([]);
});

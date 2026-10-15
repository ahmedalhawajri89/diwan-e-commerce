<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Rebuilds the SVG sprite from the icons the application actually references.
 *
 * The project used to ship the whole Bootstrap Icons webfont — 2,078 CSS
 * classes and a 131 KB woff2 carrying every glyph in the set — to draw about
 * thirty icons. This command pulls those thirty out of node_modules and writes
 * them into one sprite.
 *
 * The set comes from config/icons.php rather than from a scan of the views:
 * three call sites choose an icon at runtime, and a scanner would miss them
 * and ship a blank square that every test still passed. That file explains
 * which, and why.
 *
 * Run it after editing config/icons.php. `--check` fails instead of writing,
 * which is what the guard in DesignSystemTest uses to catch a stale sprite.
 */
class BuildIconSprite extends Command
{
    protected $signature = 'icons:build {--check : Report what would change without writing}';

    protected $description = 'Rebuild the SVG icon sprite from the icons the views reference';

    public const SPRITE = 'resources/views/partials/icon-sprite.blade.php';

    private const SOURCE = 'node_modules/bootstrap-icons/icons';

    public function handle(): int
    {
        $source = base_path(self::SOURCE);

        if (! is_dir($source)) {
            $this->error(self::SOURCE.' is missing. Run `npm install` first.');

            return self::FAILURE;
        }

        $names = self::referencedIcons();
        $this->info(count($names).' icons referenced.');

        $missing = array_values(array_filter(
            $names,
            fn ($n) => ! is_file("{$source}/{$n}.svg")
        ));

        if ($missing) {
            $this->error('No such icon in Bootstrap Icons: '.implode(', ', $missing));

            return self::FAILURE;
        }

        $sprite = $this->render($names, $source);
        $target = base_path(self::SPRITE);
        $current = is_file($target) ? file_get_contents($target) : null;

        if ($sprite === $current) {
            $this->info('Sprite is already up to date.');

            return self::SUCCESS;
        }

        if ($this->option('check')) {
            $this->warn('Sprite is out of date. Run `php artisan icons:build`.');

            return self::FAILURE;
        }

        file_put_contents($target, $sprite);
        $this->info('Wrote '.self::SPRITE.' — '.strlen($sprite).' bytes.');

        return self::SUCCESS;
    }

    /**
     * The declared icon set.
     *
     * @return list<string>
     */
    public static function referencedIcons(): array
    {
        $names = array_values(array_unique(config('icons', [])));
        sort($names);

        return $names;
    }

    /** @param  list<string>  $names */
    private function render(array $names, string $source): string
    {
        $symbols = [];

        foreach ($names as $name) {
            $svg = file_get_contents("{$source}/{$name}.svg");

            preg_match('/viewBox="([^"]+)"/', $svg, $vb);

            $body = preg_replace('/^.*?<svg[^>]*>/s', '', $svg);
            $body = preg_replace('/<\/svg>\s*$/s', '', $body);
            $body = preg_replace('/\n\s+/', '', trim($body));

            $symbols[] = '  <symbol id="i-'.$name.'" viewBox="'.$vb[1].'">'.$body.'</symbol>';
        }

        $count = count($names);
        $list = implode(', ', $names);

        return <<<BLADE
        {{--
          The icon sprite — every glyph the application actually uses, and no others.

          This replaced the Bootstrap Icons webfont, which cost 98 KB of CSS defining
          2,078 icon classes plus a 131 KB woff2 carrying every glyph in the set, to
          draw the {$count} this app draws. A webfont also blocks: the icon is invisible
          until the font arrives, and it is a separate request nothing can inline.

          Rendered once per page, hidden, and referenced by <x-icon> through <use>, so
          each use site costs about forty bytes rather than a path.

          Regenerate with:  php artisan icons:build
          Do not hand-edit — the command overwrites this file from node_modules.

          Icons: {$list}
        --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="sr-only" aria-hidden="true" focusable="false"><defs>

        BLADE.implode("\n", $symbols)."\n</defs></svg>\n";
    }
}

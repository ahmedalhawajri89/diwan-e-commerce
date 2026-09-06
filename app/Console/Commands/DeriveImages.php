<?php

namespace App\Console\Commands;

use App\Support\ImageDeriver;
use App\Support\Responsive;
use Illuminate\Console\Command;

/**
 * Builds the responsive derivatives every photograph on the site is served from.
 *
 * A furniture shop is photography. This one was shipping one JPEG per picture,
 * at one size, to every device — a 1920px hero to a phone that draws it 360px
 * wide, and a 620px product photograph to a card that shows it at 288. There
 * was no `srcset` anywhere in the project and no format newer than JPEG.
 *
 * The encoding itself lives in App\Support\ImageDeriver, because the admin's
 * product form calls it too: an upload has to get its derivatives when it
 * lands, not at the next deploy.
 *
 * The result is recorded in a manifest so rendering a page costs one file read
 * rather than a `file_exists` per image per format per width — a catalogue
 * page draws twelve photographs, which would otherwise be 120 stat calls.
 *
 *   php artisan images:derive           build what is missing
 *   php artisan images:derive --force   rebuild everything
 *   php artisan images:derive --check   fail if anything is missing (CI)
 *   php artisan images:derive --no-avif WebP only, much faster to encode
 */
class DeriveImages extends Command
{
    protected $signature = 'images:derive
        {--force : Rebuild derivatives that already exist}
        {--check : Report what is missing and fail, without writing}
        {--no-avif : Skip AVIF, which is far slower to encode}';

    protected $description = 'Generate AVIF and WebP derivatives for every photograph, at the widths the layouts use';

    public function handle(): int
    {
        $wantsAvif = ! $this->option('no-avif');
        $formats = ImageDeriver::formats($wantsAvif);

        if (! $formats) {
            $this->error('GD in this PHP build supports neither WebP nor AVIF.');

            return self::FAILURE;
        }

        if ($wantsAvif && ! in_array('avif', $formats, true)) {
            $this->warn('GD has no AVIF support in this build — writing WebP only.');
        }

        $check = $this->option('check');
        $manifest = [];
        $written = $skipped = $missing = 0;
        $unreadable = [];

        foreach (Responsive::sourceFiles() as $file) {
            $result = ImageDeriver::derive($file, $formats, (bool) $this->option('force'), $check);

            if ($result === null) {
                $unreadable[] = $file;

                continue;
            }

            $manifest[Responsive::keyFor($file)] = $result['entry'];
            $written += $result['written'];
            $skipped += $result['skipped'];
            $missing += $result['missing'];
        }

        foreach ($unreadable as $file) {
            $this->warn("Could not read {$file}");
        }

        if ($check) {
            if ($missing > 0) {
                $this->error("{$missing} derivative(s) missing. Run `php artisan images:derive`.");

                return self::FAILURE;
            }

            $this->info('All derivatives present.');

            return self::SUCCESS;
        }

        ksort($manifest);
        file_put_contents(
            Responsive::manifestPath(),
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
        );

        $this->info("{$written} written, {$skipped} already present, ".count($manifest).' sources in the manifest.');

        return self::SUCCESS;
    }
}

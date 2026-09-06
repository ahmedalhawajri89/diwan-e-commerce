<?php

namespace App\Support;

/**
 * Writes the AVIF and WebP derivatives of one photograph.
 *
 * Shared by two callers with different rhythms: `images:derive` walks every
 * source at deploy time, and the admin's product form derives a single upload
 * the moment it lands. Both must produce identical files, so the encoding
 * lives here rather than in either of them.
 */
final class ImageDeriver
{
    /**
     * The widths, chosen from what the layouts ask for rather than from a
     * round sequence: 320 and 480 are phones; 640 covers a product card at 2x
     * and the diptych's photograph on a tablet; 960 and 1280 cover the diptych
     * and the product page at 1x and 2x; 1600 is the lookbook's full-bleed.
     */
    public const WIDTHS = [320, 480, 640, 960, 1280, 1600];

    private const QUALITY = ['webp' => 82, 'avif' => 55];

    /** @return list<string> */
    public static function formats(bool $avif = true): array
    {
        $formats = [];

        if ($avif && function_exists('imageavif')) {
            $formats[] = 'avif';
        }

        if (function_exists('imagewebp')) {
            $formats[] = 'webp';
        }

        return $formats;
    }

    /**
     * Derive one source.
     *
     * @param  list<string>  $formats
     * @param  bool  $dryRun  count what is missing instead of writing it
     * @return array{entry: array, written: int, skipped: int, missing: int}|null
     */
    public static function derive(
        string $file,
        array $formats,
        bool $force = false,
        bool $dryRun = false
    ): ?array {
        $image = self::open($file);

        if ($image === null) {
            return null;
        }

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);

        // Never wider than the original: an upscale invents detail, and costs
        // more bytes than the picture it came from.
        $widths = array_values(array_filter(self::WIDTHS, fn ($w) => $w < $sourceWidth));
        $widths[] = $sourceWidth;

        $derivedDir = dirname($file).'/'.Responsive::DERIVED_DIR;

        if (! $dryRun && ! is_dir($derivedDir)) {
            mkdir($derivedDir, 0755, true);
        }

        $stem = pathinfo($file, PATHINFO_FILENAME);
        $entry = ['width' => $sourceWidth, 'height' => $sourceHeight, 'formats' => []];
        $written = $skipped = $missing = 0;

        foreach ($formats as $format) {
            foreach ($widths as $width) {
                $target = "{$derivedDir}/{$stem}-{$width}.{$format}";

                if (is_file($target) && ! $force && ! self::isStale($file, $target)) {
                    $skipped++;
                    $entry['formats'][$format][] = $width;

                    continue;
                }

                if ($dryRun) {
                    $missing++;

                    continue;
                }

                if (self::write($image, $target, $format, $width, $sourceWidth, $sourceHeight)) {
                    $written++;
                    $entry['formats'][$format][] = $width;
                }
            }
        }

        return compact('entry', 'written', 'skipped', 'missing');
    }

    /**
     * Derive one file and fold it into the manifest, for a photograph that
     * arrives after the deploy-time pass — an admin's upload.
     *
     * Failure is deliberately quiet: a product whose picture has no
     * derivatives still shows the picture, and an encoder problem must not
     * lose the upload the operator just made. Nothing here is load-bearing.
     */
    public static function deriveAndRecord(string $file): void
    {
        try {
            $result = self::derive($file, self::formats());

            if ($result === null) {
                return;
            }

            $path = Responsive::manifestPath();
            $manifest = is_file($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];
            $manifest[Responsive::keyFor($file)] = $result['entry'];
            ksort($manifest);

            file_put_contents(
                $path,
                json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n",
                LOCK_EX
            );

            Responsive::forget();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Whether a derivative predates the photograph it was made from.
     *
     * Without this the command only asked "does the file exist?", so replacing
     * a photograph and re-running it reported "already present" and served the
     * old picture at every width — the source JPEG updated, every AVIF and
     * WebP the browser actually fetches did not. Silent, and invisible until
     * someone wondered why the new photograph had not appeared.
     */
    private static function isStale(string $source, string $derivative): bool
    {
        return filemtime($source) > filemtime($derivative);
    }

    private static function open(string $file): ?\GdImage
    {
        $image = match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($file),
            'png' => @imagecreatefrompng($file),
            'webp' => @imagecreatefromwebp($file),
            default => false,
        };

        return $image ?: null;
    }

    private static function write(
        \GdImage $source,
        string $target,
        string $format,
        int $width,
        int $sourceWidth,
        int $sourceHeight
    ): bool {
        $height = (int) round($sourceHeight * ($width / $sourceWidth));
        $canvas = imagecreatetruecolor($width, $height);

        // Keep transparency through the resample; a PNG resized onto an opaque
        // canvas comes back with a black box behind it.
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        // No imagedestroy(): GdImage is garbage-collected since PHP 8, and the
        // function is deprecated in 8.4.
        return match ($format) {
            'webp' => imagewebp($canvas, $target, self::QUALITY['webp']),
            'avif' => imageavif($canvas, $target, self::QUALITY['avif']),
            default => false,
        };
    }
}

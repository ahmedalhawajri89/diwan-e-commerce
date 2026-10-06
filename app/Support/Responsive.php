<?php

namespace App\Support;

/**
 * Resolves a photograph to the set of sizes and formats it can be served in.
 *
 * The `<x-picture>` component asks this what exists; `images:derive` writes it.
 * Both sides agree through a manifest rather than through the filesystem: a
 * catalogue page draws twelve photographs, and asking the disk whether each
 * one has five widths in two formats is 120 stat calls to render a grid.
 *
 * A source with no entry in the manifest is not an error. An admin uploads a
 * photograph and it is on the page before any derivative exists, so the
 * component falls back to the original file. The picture is heavier than it
 * needs to be until the next `images:derive`; it is never missing.
 */
final class Responsive
{
    /** Derivatives live beside their source, in a directory of this name. */
    public const DERIVED_DIR = '_derived';

    /** Newest format first: a browser takes the first <source> it understands. */
    public const FORMATS = ['avif' => 'image/avif', 'webp' => 'image/webp'];

    private static ?array $manifest = null;

    public static function manifestPath(): string
    {
        return public_path('assets/derived-manifest.json');
    }

    /**
     * Every image the derive command should process.
     *
     * @return list<string>
     */
    public static function sourceFiles(): array
    {
        $roots = [
            public_path('assets/images'),
            storage_path('app/public/products'),
        ];

        $files = [];

        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($it as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                // Never derive from a derivative.
                if (str_contains(str_replace('\\', '/', $file->getPathname()), '/'.self::DERIVED_DIR.'/')) {
                    continue;
                }

                if (in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png'], true)) {
                    $files[] = str_replace('\\', '/', $file->getPathname());
                }
            }
        }

        sort($files);

        return $files;
    }

    /**
     * The manifest key for a source file: its URL path, without the origin.
     *
     * e.g. `assets/images/about.jpg`, or `storage/products/sofa.jpg`.
     */
    public static function keyFor(string $absolutePath): string
    {
        $path = str_replace('\\', '/', $absolutePath);
        $public = str_replace('\\', '/', public_path());
        $storage = str_replace('\\', '/', storage_path('app/public'));

        if (str_starts_with($path, $public)) {
            return ltrim(substr($path, strlen($public)), '/');
        }

        if (str_starts_with($path, $storage)) {
            return 'storage/'.ltrim(substr($path, strlen($storage)), '/');
        }

        return basename($path);
    }

    /** @return array<string, array> */
    public static function manifest(): array
    {
        if (self::$manifest !== null) {
            return self::$manifest;
        }

        $file = self::manifestPath();

        return self::$manifest = is_file($file)
            ? (json_decode(file_get_contents($file), true) ?: [])
            : [];
    }

    /** Drop the memoised manifest. For tests that rebuild it mid-run. */
    public static function forget(): void
    {
        self::$manifest = null;
    }

    /**
     * The smallest derived file for an image, for places a script sets the
     * src (the bag drawer) and a <picture> cannot be used. The original when
     * nothing is derived.
     */
    public static function smallest(string $src): string
    {
        $sources = self::for($src)['sources'];
        $srcset = $sources['image/webp'] ?? reset($sources) ?: null;

        return $srcset ? strtok(trim(explode(',', $srcset)[0]), ' ') : $src;
    }

    /**
     * What the <picture> element needs for one image.
     *
     * @return array{
     *     src: string,
     *     sources: array<string, string>,
     *     width: int|null,
     *     height: int|null
     * }  `sources` maps a MIME type to a srcset; empty when nothing is derived.
     */
    public static function for(string $src): array
    {
        $key = self::normalise($src);
        $entry = self::manifest()[$key] ?? null;

        $result = [
            'src' => $src,
            'sources' => [],
            'width' => $entry['width'] ?? null,
            'height' => $entry['height'] ?? null,
        ];

        if ($entry === null) {
            return $result;
        }

        $stem = pathinfo($key, PATHINFO_FILENAME);
        $dir = trim(dirname($key), '.');
        $base = ($dir === '' ? '' : $dir.'/').self::DERIVED_DIR.'/';

        foreach (self::FORMATS as $format => $mime) {
            $widths = $entry['formats'][$format] ?? [];

            if (! $widths) {
                continue;
            }

            sort($widths);

            $result['sources'][$mime] = implode(', ', array_map(
                fn ($w) => asset("{$base}{$stem}-{$w}.{$format}")." {$w}w",
                $widths
            ));
        }

        return $result;
    }

    /**
     * Reduce whatever a view handed us to a manifest key.
     *
     * Callers pass a full URL from `asset()`, a root-relative path, or a bare
     * `assets/...` string, because `ProductImage::url` already normalises three
     * shapes of its own and the component should not care which arrived.
     */
    private static function normalise(string $src): string
    {
        $src = strtok($src, '?#') ?: $src;

        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
            $path = parse_url($src, PHP_URL_PATH) ?: '';
        } else {
            $path = $src;
        }

        $base = parse_url(config('app.url'), PHP_URL_PATH);

        if ($base && $base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        return ltrim($path, '/');
    }
}

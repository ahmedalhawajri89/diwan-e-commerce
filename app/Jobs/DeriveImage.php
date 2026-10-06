<?php

namespace App\Jobs;

use App\Support\ImageDeriver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

/**
 * Writes the AVIF and WebP sizes of one uploaded photograph, off the request.
 *
 * Encoding twelve files took about fifty seconds per photograph inside the
 * save, so a product with three photographs held the operator on a spinning
 * button for minutes. The upload is stored and the page answers at once;
 * this runs on the queue (`composer dev` starts a worker). Until it has run
 * the picture component serves the original file, which is a slower picture,
 * never a missing one.
 */
class DeriveImage implements ShouldQueue
{
    use Queueable;

    /** One photograph should never take this long; a stuck encoder is let go. */
    public int $timeout = 300;

    public int $tries = 2;

    /** @param  string  $path  on the public disk, e.g. products/abc.jpg */
    public function __construct(public string $path) {}

    public function handle(): void
    {
        $disk = Storage::disk('public');

        // Deleted between the upload and this job: nothing to derive.
        if (! $disk->exists($this->path)) {
            return;
        }

        ImageDeriver::deriveAndRecord($disk->path($this->path));
    }
}

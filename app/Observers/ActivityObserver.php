<?php

namespace App\Observers;

use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Support\Activity;
use Illuminate\Database\Eloquent\Model;

/**
 * Hears every create, change and delete on the models the panel edits and
 * hands it to App\Support\Activity, which writes it only when an
 * administrator did it. A bulk action passes through here once per row,
 * because each row is saved on its own.
 */
class ActivityObserver
{
    public function created(Model $model): void
    {
        Activity::record('created', $model, $this->context($model));
    }

    public function updated(Model $model): void
    {
        $diff = Activity::diff($model);

        if ($diff !== []) {
            Activity::record('updated', $model, $diff + $this->context($model));
        }
    }

    public function deleted(Model $model): void
    {
        Activity::record('deleted', $model, $this->context($model));
    }

    /**
     * A colour or a photograph has no page of its own; remember the piece it
     * belongs to, so the line can lead there. Kept under a key no column
     * shares.
     */
    private function context(Model $model): array
    {
        return $model instanceof ProductVariant || $model instanceof ProductImage
            ? ['_product' => $model->product_id]
            : [];
    }
}

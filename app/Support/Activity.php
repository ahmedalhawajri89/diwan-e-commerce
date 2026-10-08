<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Contact;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes the activity log: what an administrator did, to what, and what it
 * changed. A customer's own actions (an order, a review, an account) are not
 * written here; the notices and each order's history already hold them.
 *
 * Never throws: the log describes the work, it must never stop it.
 */
final class Activity
{
    /** Columns whose value is never worth a line, or never safe to keep. */
    private const IGNORED = ['created_at', 'updated_at', 'password', 'remember_token', 'email_verified_at'];

    /** @param  array<string, mixed>  $changes */
    public static function record(string $action, ?Model $entity = null, array $changes = [], ?User $actor = null): void
    {
        try {
            $actor ??= auth()->user();

            if (! $actor instanceof User || ! $actor->isAdmin()) {
                return;
            }

            ActivityLog::create([
                'user_id' => $actor->getKey(),
                'action' => $action,
                'entity_type' => $entity ? $entity::class : 'session',
                'entity_id' => $entity?->getKey(),
                'entity_label' => $entity ? mb_substr(self::label($entity), 0, 255) : null,
                'changes' => $changes ?: null,
                // A command or a queued job has no visitor's address; a test does.
                'ip_address' => app()->runningInConsole() && ! app()->runningUnitTests() ? null : request()->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * What changed in a model just saved: each meaningful field, before and
     * after. Hidden attributes (a password) and timestamps are left out.
     *
     * @return array<string, array{0: mixed, 1: mixed}>
     */
    public static function diff(Model $model): array
    {
        $skip = [...self::IGNORED, ...$model->getHidden()];
        $diff = [];

        foreach ($model->getChanges() as $field => $after) {
            if (in_array($field, $skip, true)) {
                continue;
            }

            $before = $model->getOriginal($field);
            if ((string) $before === (string) $after) {
                continue;
            }

            $diff[$field] = [self::plain($before), self::plain($after)];
        }

        return $diff;
    }

    /** What to call it in a sentence, at the time it happened. */
    public static function label(Model $entity): string
    {
        return match (true) {
            $entity instanceof Product => (string) $entity->raw('name'),
            $entity instanceof Category => (string) $entity->raw('name'),
            $entity instanceof ProductVariant => trim(($entity->product?->raw('name') ?? '').' · '.($entity->colourLabel() ?? '#'.$entity->getKey()), ' ·'),
            $entity instanceof ProductImage => (string) ($entity->product?->raw('name') ?? '#'.$entity->getKey()),
            $entity instanceof Order => $entity->number(),
            $entity instanceof User => (string) $entity->name,
            $entity instanceof Review => trim(($entity->user?->name ?? '').' · '.($entity->product?->raw('name') ?? ''), ' ·'),
            $entity instanceof Contact => (string) ($entity->subject ?: $entity->name),
            $entity instanceof NewsletterSubscriber => (string) $entity->email,
            default => class_basename($entity).' #'.$entity->getKey(),
        };
    }

    private static function plain(mixed $value): mixed
    {
        return match (true) {
            $value instanceof \DateTimeInterface => $value->format('Y-m-d H:i'),
            is_array($value) || is_object($value) => json_encode($value, JSON_UNESCAPED_UNICODE),
            is_string($value) && mb_strlen($value) > 300 => mb_substr($value, 0, 300).'…',
            default => $value,
        };
    }
}

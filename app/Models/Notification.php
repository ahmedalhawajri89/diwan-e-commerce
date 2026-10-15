<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Something an administrator should know about: an order placed, a colour
 * running low, a review or a message waiting, a customer who joined.
 *
 * One row per administrator, so each reads their own. The words are not
 * stored: `type` and `data` are, and text() writes the sentence in the
 * language of whoever is reading it now. `title` and `message` keep an
 * English copy only because the columns are required.
 *
 * Not Laravel's DatabaseNotification: this table predates that and has a
 * different shape (an integer key, title and message columns), so it is
 * read through User::alerts(), never User::notifications().
 */
class Notification extends Model
{
    /** What can be said, each with the glyph beside it and where it leads. */
    public const TYPES = [
        'order_placed' => ['icon' => 'receipt', 'tone' => 'accent'],
        'low_stock' => ['icon' => 'box-seam', 'tone' => 'warn'],
        'review_pending' => ['icon' => 'star', 'tone' => 'neutral'],
        'contact_received' => ['icon' => 'envelope', 'tone' => 'neutral'],
        'customer_registered' => ['icon' => 'person', 'tone' => 'ok'],
        'return_requested' => ['icon' => 'arrow-return-left', 'tone' => 'warn'],
    ];

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /** The sentence, in the reader's language. */
    public function text(): string
    {
        return __('admin.alerts.'.$this->type, $this->placeholders());
    }

    public function icon(): string
    {
        return self::TYPES[$this->type]['icon'] ?? 'grid';
    }

    public function tone(): string
    {
        return self::TYPES[$this->type]['tone'] ?? 'neutral';
    }

    /** Where reading it takes you: the thing it is about. */
    public function url(): string
    {
        $data = $this->data ?? [];

        return match ($this->type) {
            'order_placed' => isset($data['order_id']) ? route('admin.orders.show', $data['order_id']) : route('admin.orders.index'),
            'low_stock' => isset($data['product_id']) ? route('admin.products.edit', $data['product_id']) : route('admin.products.index', ['need' => 'low_stock']),
            'review_pending' => route('admin.reviews.index', ['status' => 'pending']),
            'contact_received' => route('admin.contacts.index'),
            'return_requested' => route('admin.returns.index', ['status' => 'requested']),
            'customer_registered' => isset($data['user_id']) ? route('admin.users.show', $data['user_id']) : route('admin.users.index'),
            default => route('admin.dashboard'),
        };
    }

    /** The shape the bell's script reads. */
    public function toFeed(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'text' => $this->text(),
            'url' => $this->url(),
            'icon' => $this->icon(),
            'tone' => $this->tone(),
            'unread' => $this->isUnread(),
            'when' => $this->created_at?->diffForHumans(),
            'at' => $this->created_at?->toIso8601String(),
        ];
    }

    /** @return array<string, string> */
    private function placeholders(): array
    {
        return collect($this->data ?? [])
            ->map(fn ($value) => is_scalar($value) ? (string) $value : '')
            ->all();
    }
}

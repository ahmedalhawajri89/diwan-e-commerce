<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * One thing an administrator did in the panel: created, changed or deleted
 * something (with each changed field, before and after), or signed in.
 * Written by App\Support\Activity only; kept for a year (`model:prune`).
 */
class ActivityLog extends Model
{
    use MassPrunable;

    /** What the page's type chips group the lines into. */
    public const KINDS = [
        'catalogue' => [Product::class, ProductVariant::class, ProductImage::class, Category::class],
        'orders' => [Order::class],
        'reviews' => [Review::class],
        'people' => [User::class, Contact::class, NewsletterSubscriber::class],
        'access' => ['session'],
    ];

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'entity_label',
        'changes',
        'ip_address',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** A year, then gone. */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subYear());
    }

    /** The short name of what it was about: "product", "order", "session". */
    public function subject(): string
    {
        return match ($this->entity_type) {
            Product::class => 'product',
            ProductVariant::class => 'variant',
            ProductImage::class => 'image',
            Category::class => 'category',
            Order::class => 'order',
            Review::class => 'review',
            User::class => 'user',
            Contact::class => 'message',
            NewsletterSubscriber::class => 'subscriber',
            default => 'session',
        };
    }

    /** Where the thing is now, if it still exists and has a page. */
    public function url(): ?string
    {
        if (! $this->entity_id || $this->action === 'deleted') {
            return null;
        }

        return match ($this->entity_type) {
            Product::class => route('admin.products.show', $this->entity_id),
            Category::class => route('admin.categories.edit', $this->entity_id),
            Order::class => route('admin.orders.show', $this->entity_id),
            User::class => route('admin.users.show', $this->entity_id),
            Review::class => route('admin.reviews.index'),
            Contact::class => route('admin.contacts.index'),
            ProductVariant::class, ProductImage::class => ($productId = $this->changes['_product'] ?? null) ? route('admin.products.show', $productId) : null,
            default => null,
        };
    }
}

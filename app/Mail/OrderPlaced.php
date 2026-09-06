<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The receipt a customer gets for placing an order.
 *
 * Until now they got nothing. They pressed "Place order", saw a thank-you page,
 * and that was the entire record of a purchase — nothing in their inbox, nothing
 * to forward, nothing to find again in six months.
 *
 * Sent in the customer's locale rather than the server's default: the person who
 * shopped in Arabic does not want their receipt in English. The locale is
 * captured when the mail is built, because a queued job runs later, in a worker
 * that knows nothing about the request that queued it.
 *
 * What it deliberately does NOT say: when the order will arrive. Nothing in this
 * application knows that, and design.md's honesty rules put an invented delivery
 * date in the same class as an invented dimension.
 */
class OrderPlaced extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $language,
    ) {
        // `locale()` is Mailable's own; `$language` is ours, because the
        // parent already declares an untyped `$locale` and redeclaring it
        // with a type is a fatal error.
        $this->locale($language);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('diwan.mail.placed.subject', ['number' => $this->order->id], $this->language),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-placed',
            with: ['order' => $this->order],
        );
    }
}

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
 * Tells a customer their order moved.
 *
 * The `orders` table has always had a full lifecycle — pending, processing,
 * shipped, delivered, cancelled — and the admin panel has always been able to
 * advance it. Nobody told the customer. An operator marked an order "shipped"
 * and the only person who could see that was the operator.
 *
 * Only the transitions a customer can act on send mail. See NOTIFIABLE.
 */
class OrderStatusChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The statuses worth an email, and nothing else.
     *
     * `pending` is the state an order is created in, and OrderPlaced already
     * covers that — mailing it again would be two messages for one event.
     * `processing` is an internal step: "we have started" tells the customer
     * nothing they can do anything with, and a shop that emails on every
     * internal move trains people to ignore its email.
     */
    public const NOTIFIABLE = ['shipped', 'delivered', 'cancelled'];

    public function __construct(
        public Order $order,
        public string $status,
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
            subject: __(
                "diwan.mail.status.{$this->status}.subject",
                ['number' => $this->order->reference()],
                $this->language
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status',
            with: [
                'order' => $this->order,
                'status' => $this->status,
            ],
        );
    }
}

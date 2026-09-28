<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $customerName,
        public readonly string $heading,
        public readonly string $intro,
        public readonly string $ctaUrl,
        public readonly string $ctaLabel,
        public readonly string $kind,
    ) {}

    public function envelope(): Envelope
    {
        $suffix = $this->kind === 'cancelled' ? 'cancelled' : 'delivered';

        return new Envelope(
            subject: 'Order '.$this->order->order_number.' '.$suffix.' | '.config('brand.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status',
        );
    }
}

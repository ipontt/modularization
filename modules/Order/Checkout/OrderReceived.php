<?php

namespace Modules\Order\Checkout;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderReceived extends Mailable
{
    use Queueable;

    public function __construct(
        public string $localizedTotal,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Received',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "We have received your order of $this->localizedTotal.",
        );
    }
}

<?php

namespace Modules\Order\Checkout;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Modules\Order\Contracts\OrderDTO;

class PaymentForOrderFailed extends Mailable
{
    use Queueable;

    public function __construct(
        public OrderDTO $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Failed',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "Your order of {$this->order->localizedTotal} couldn't be processed.",
        );
    }
}

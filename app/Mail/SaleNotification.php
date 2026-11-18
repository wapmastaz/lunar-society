<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the office about every completed payment, already sorted into its class.
 */
class SaleNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $who = $this->order->buyer_name
            ?: trim($this->order->payer_first_name.' '.$this->order->payer_last_name)
            ?: $this->order->email;

        return new Envelope(
            subject: "New payment: {$this->order->classLabel()}, {$this->order->amountLabel()}, {$who}",
            replyTo: [new Address($this->order->email)],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.sale-notification');
    }
}

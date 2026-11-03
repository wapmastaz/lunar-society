<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $subject = $this->order->isFounder()
            ? "Welcome to the Frontier, Founder {$this->order->slotLabel()}! Your Lunar Society confirmation"
            : 'Your '.config('lunar.brand').' order is confirmed';

        return new Envelope(
            subject: $subject,
            replyTo: [new Address((string) config('lunar.email'), config('lunar.brand'))],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: $this->order->isFounder() ? 'mail.founder-welcome' : 'mail.souvenir-receipt');
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->order->isFounder()) {
            return [];
        }

        return [
            Attachment::fromData(
                fn () => Pdf::loadView('pdf.certificate', ['order' => $this->order])->setPaper('letter', 'landscape')->output(),
                'Lunar-Society-Certificate-'.$this->order->slot_number.'.pdf',
            )->withMime('application/pdf'),
        ];
    }
}

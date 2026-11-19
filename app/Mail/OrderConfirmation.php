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
            ? "Welcome to the Frontier, Founder {$this->order->slotLabel()}! Your Lunar Society Network confirmation"
            : ($this->isMemberEcard()
                ? 'Your welcome e-card: you are a New Lunar Society Member!'
                : ($this->order->product?->ships
                ? 'Your order with '.config('lunar.brand').' is confirmed'
                : 'Thank you for supporting the Lunar Society Network'));

        return new Envelope(
            subject: $subject,
            replyTo: [new Address((string) config('lunar.email'), config('lunar.brand'))],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: match (true) {
            $this->order->isFounder() => 'mail.founder-welcome',
            $this->isMemberEcard() => 'mail.member-ecard',
            default => 'mail.souvenir-receipt',
        });
    }

    /**
     * The $79 New Lunar Society Member gets a welcome e-card instead of a receipt.
     */
    protected function isMemberEcard(): bool
    {
        return $this->order->product?->slug === 'member';
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

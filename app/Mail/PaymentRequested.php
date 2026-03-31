<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pagamento pendente — {$this->booking->dog->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-requested',
            with: ['booking' => $this->booking],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
    }

    public function envelope(): Envelope
    {
        $status = $this->booking->status === 'aprovado' ? 'Aprovado' : 'Recusado';
        $dog = $this->booking->dog->name;
        $type = match($this->booking->type) {
            'hotel' => 'Estadia',
            'aula'  => 'Aula',
            'atl'   => 'ATL',
        };

        return new Envelope(
            subject: "Pedido de {$type} para {$dog} — {$status}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking-status',
            with: [
                'booking' => $this->booking,
                'typeLabel' => match($this->booking->type) {
                    'hotel' => 'Estadia',
                    'aula'  => 'Aula',
                    'atl'   => 'ATL',
                },
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

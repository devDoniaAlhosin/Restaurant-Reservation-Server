<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class BookingStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $status;
    public $paymentLink;  // Add payment link variable

    public function __construct(Booking $booking, $status, $paymentLink = null)
    {
        $this->booking = $booking;
        $this->status = $status;
        $this->paymentLink = $paymentLink;  // Assign the payment link
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Status Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking_status',
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Booking Status Update')
                    ->view('emails.booking_status')
                    ->with([
                        'booking' => $this->booking,
                        'status' => $this->status,
                        'paymentLink' => $this->paymentLink,  // Pass payment link to the view
                    ]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

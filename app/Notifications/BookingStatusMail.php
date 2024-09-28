<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusMail extends Notification
{
    use Queueable;


    public $booking;
    public $messageContent;

    public function __construct(Booking $booking, $messageContent)
    {
        $this->booking = $booking;
        $this->messageContent = $messageContent;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->booking->status;
        $subject = "Your Booking has been " . ucfirst($status);

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->line($this->messageContent)
            ->line("Booking Details:")
            ->line("Date and Time: {$this->booking->date_time}")
            ->line("Number of People: {$this->booking->total_person}");

        if ($status === 'accepted') {
            $mailMessage->line("Payment Details: [Include payment info here]");
        } else {
            $mailMessage->line("Suggested Date/Time: [Include alternative suggestions here]");
        }

        return $mailMessage;
    
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

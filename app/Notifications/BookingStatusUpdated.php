<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

class BookingStatusUpdated extends Notification
{
    use Queueable;

    protected $booking;
    protected $message;

    public function __construct(Booking $booking, $message)
    {
        $this->booking = $booking;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail']; // Use 'database' if you want to store it in the database as well
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Booking Status Updated')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line($this->message)
                    ->action('View Booking', url('/bookings/' . $this->booking->id))
                    ->line('Thank you for using our restaurant reservation system!');
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->id,
            'status' => $this->booking->status,
            'message' => $this->message,
        ];
    }
}



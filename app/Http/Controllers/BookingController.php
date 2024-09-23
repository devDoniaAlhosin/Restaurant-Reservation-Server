<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
class BookingController extends Controller
{
    public function __construct()
{
    $this->middleware('auth:sanctum')->only(['userBooking']);
}

    public function userBooking(Request $request){
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string','max:15'],
            'date' => ['required', 'date'],
            'time' => ['required','date_format:H:i'],
            'total_person' => ['required', 'integer', 'min:1'],

        ]);
        $booking = new Booking([
            'user_id' => $request->user()->id,
            'username' => $request->username,
            'phone' => $request->phone,
            'date_time' => $request->date . ' ' . $request->time,
            'total_person' => $request->total_person,
            'status' => 'pending',
            'notes' => $request->input('notes', '')
        ]);

        $booking->save();
        return response()->json(['message' => 'Booking created successfully'], 201);

    }


    public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => ['required', 'in:accepted,rejected'],
        'notes' => ['nullable', 'string']
    ]);

    $booking = Booking::findOrFail($id);

    // Update booking status
    $booking->status = $request->status;
    $booking->notes = $request->notes;
    $booking->save();

    // Notify user
    $user = $booking->user;
    $this->sendNotification($user, $booking);

    return response()->json(['message' => 'Booking status updated successfully']);
}







    // // Method to send notification email to the user

    // protected function sendNotification(User $user, Booking $booking)
    // {
    //     $status = $booking->status;
    //     $message = ($status === 'accepted')
    //                 ? "Your booking has been accepted."
    //                 : "Your booking has been rejected.";

    //     // Send Email Notification
    //     Mail::to($user->email)->send(new \App\Mail\BookingStatusUpdated($booking, $message));

    //     // Store a notification in the database for frontend display
    //     $user->notifications()->create([
    //         'message' => $message,
    //         'is_read' => false,
    //     ]);
    // }
    protected function sendNotification(User $user, Booking $booking)
{
    $status = $booking->status;
    $message = ($status === 'accepted')
                ? "Your booking has been accepted."
                : "Your booking has been rejected.";

    // Send the notification to the user via email
    $user->notify(new \App\Notifications\BookingStatusUpdated($booking, $message));

    // Store the notification in the database if you want to show it on the frontend
    $user->notifications()->create([
        'message' => $message,
        'is_read' => false,
    ]);
}

}

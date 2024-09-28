<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Notifications\BookingStatusMail;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['userBooking']);
    }

    public function userBooking(Request $request)
    {
        // Get current logged-in user
        $user = $request->user();

        // Validate booking details
        $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value !== $user->username) {
                        $fail('The ' . $attribute . ' must match the logged-in user\'s username.');
                    }
                }
            ],
            'phone' => [
                'required',
                'string',
                'max:15',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value !== $user->phone) {
                        $fail('The ' . $attribute . ' must match the logged-in user\'s phone number.');
                    }
                }
            ],
            'date' => [
                'required',
                'date',
                'after_or_equal:today', // No past dates
                'before_or_equal:' . Carbon::now()->addMonths(3)->format('Y-m-d') // Limit to 3 months in advance
            ],
            'time' => ['required', 'date_format:h:i A'], // Enforce '11:20 PM' format
            'total_person' => ['required', 'integer', 'min:1'],
        ]);

        // Create a new booking
        $booking = new Booking([
            'user_id' => $user->id,
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

        // Notify the user via email
        $user = $booking->user;
        $message = ($booking->status === 'accepted')
            ? "Your booking has been accepted. Below are the details."
            : "Your booking has been rejected. Please see alternative dates.";

        // Trigger the email notification
        $user->notify(new BookingStatusMail($booking, $message));

        // Mark email as sent
        $booking->email_sent = true;
        $booking->save();

        return response()->json(['message' => 'Booking status updated and email sent'], 200);
    }
}

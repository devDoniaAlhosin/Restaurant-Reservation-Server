<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
// use App\Notifications\BookingStatusMail;
use Illuminate\Support\Carbon;
use App\Mail\BookingStatusMail;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function __construct()
    {
    }

    public function userBooking(Request $request)
    {
        // Get current logged-in user
        $user = $request->user();

        // Validate booking details
        $validatedData = $request->validate([
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
            'notes' => ['nullable', 'string']
        ]);


        // Create a new booking
        // $booking = new Booking([
        //     'user_id' => $user->id,
        //     'username' => $request->username,
        //     'phone' => $request->phone,
        //     'date_time' => $request->date . ' ' . $request->time,
        //     'total_person' => $request->total_person,
        //     'status' => 'pending',
        //     'notes' => $request->input('notes', '')
        // ]);

        // $booking->save();

        // return response()->json(['message' => 'Booking created successfully'], 201);
        $booking = Booking::create([
            'user_id' => $user->id,
            'username' => $user->username,
            'phone' => $user->phone,
            'date_time' => $validatedData['date'] . ' ' . $validatedData['time'],
            'total_person' => $validatedData['total_person'],
            'status' => 'pending',
            'notes' => $validatedData['notes'] ?? '',
        ]);

        return response()->json(['message' => 'Booking created successfully'], 201);
    }
public function getallbookings(){
    $booking=Booking::all();
    if ($booking->isEmpty()) {
        return response()->json(['message' => 'No bookings are found'], 404);
    }
    return response()->json($booking,200);
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
        $messageContent = ($booking->status === 'accepted')
            ? "Your booking has been accepted. Below are the details."
            : "Your booking has been rejected. Please see alternative dates.";

        try {
            Mail::to($user->email)->send(new BookingStatusMail($booking, $messageContent));
            Log::info('Mail sent successfully to: ' . $user->email);
        } catch (\Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage());
        }


        // Mark email as sent
        $booking->email_sent = true;
        $booking->save();

        return response()->json(['message' => 'Booking status updated and email sent'], 200);
    }

    public function getUserBookings(Request $request)
    {
        $user = $request->user();

        // Retrieve bookings for the authenticated user
        $bookings = Booking::where('user_id', $user->id)->get();

        if ($bookings->isEmpty()) {
            return response()->json(['message' => 'No bookings found for this user.'], 404);
        }

        return response()->json($bookings, 200);
    }


    public function deleteBooking($id) {
        // Check if the booking exists
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'This booking request does not exist'], 404); // Use 404 for not found
        }

        // Delete the booking
        $booking->delete(); // Use the model's delete method

        return response()->json(['message' => 'This booking request has been deleted successfully'], 200); // 200 for success
    }
    // for the user to update his own data while pending
    // public function updateUserBooking(Request $request, $id)
    // {
    //     \Log::info('Updating booking with ID: ' . $id, ['request' => $request->all()]); // Log incoming data
    //     // Validate incoming request
    //     $request->validate([
    //         'date_time' => 'required|date|after:now',
    //         'total_person' => 'required|integer|min:1',
    //         'notes' => 'nullable|string',
    //     ]);

    //     // Find the booking by ID
    //     $booking = Booking::findOrFail($id);

    //     // Check if the authenticated user is the owner of the booking
    //     if ($booking->user_id !== $request->user()->id) {
    //         return response()->json(['error' => 'Unauthorized'], 403);
    //     }

    //     // Combine date_time and time to create a complete datetime
    //     $dateTime = Carbon::parse($request->date_time . ' ' . $request->time);

    //     // Update booking details
    //     $booking->date_time = $dateTime; // Store the complete datetime
    //     $booking->total_person = $request->total_person;
    //     $booking->notes = $request->notes;

    //     // Save changes
    //     $booking->save();

    //     return response()->json($booking, 200);
    // }

    public function updateUserBooking(Request $request, $id)
{
    Log::info('Updating booking with ID: ' . $id, ['request' => $request->all()]); // Log incoming data

    // Validate incoming request
    $request->validate([
        'date' => 'required|date|after:now',
        'time' => 'required|date_format:h:i A', // Ensure time is in the correct format
        'total_person' => 'required|integer|min:1',
        'notes' => 'nullable|string',
    ]);

    // Find the booking by ID
    $booking = Booking::findOrFail($id);

    // Check if the authenticated user is the owner of the booking
    if ($booking->user_id !== $request->user()->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Combine date and time to create a complete datetime
    $dateTime = Carbon::createFromFormat('Y-m-d h:i A', $request->date . ' ' . $request->time);

    // Update booking details
    $booking->date_time = $dateTime; // Store the complete datetime
    $booking->total_person = $request->total_person;
    $booking->notes = $request->notes;

    // Save changes
    $booking->save();

    return response()->json($booking, 200);
}


}

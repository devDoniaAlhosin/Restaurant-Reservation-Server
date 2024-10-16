<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Mail\BookingStatusMail;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\Checkout\Session;


class BookingController extends Controller
{
    public function __construct()
    {
    }

    /**
     * @OA\Post(
     *     path="/bookings",
     *     summary="Create a new booking",
     *     tags={"Bookings"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "phone", "date", "time", "total_person"},
     *             @OA\Property(property="username", type="string", example="john_doe"),
     *             @OA\Property(property="phone", type="string", example="123-456-7890"),
     *             @OA\Property(property="date", type="string", format="date", example="2024-10-15"),
     *             @OA\Property(property="time", type="string", example="12:00 PM"),
     *             @OA\Property(property="total_person", type="integer", example=4),
     *             @OA\Property(property="notes", type="string", example="Please prepare a vegetarian option.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Booking created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Booking created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed"
     *     )
     * )
     */
    public function userBooking(Request $request)
{
    // Validate booking details
    $validatedData = $request->validate([
        'username' => [
            'required',
            'string',
            'max:255',
            'exists:users,username', // Ensure the username exists in the users table
        ],
        'phone' => [
            'required',
            'string',
            'max:15',
            'exists:users,phone', // Ensure the phone number exists in the users table
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

    // Find the user based on the provided username and phone
    $user = User::where('username', $validatedData['username'])
                ->where('phone', $validatedData['phone'])
                ->firstOrFail(); // This will throw a 404 if the user is not found

    // Create the booking
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
/**
 * @OA\Get(
 *     path="/bookings",
 *     summary="Get all bookings",
 *     tags={"Bookings"},
 *     @OA\Response(
 *         response=200,
 *         description="A list of bookings",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Booking")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No bookings found",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="No bookings are found"
 *             )
 *         )
 *     )
 * )
 */
public function getallbookings(){
    $booking=Booking::all();
    if ($booking->isEmpty()) {
        return response()->json(['message' => 'No bookings are found'], 404);
    }
    return response()->json($booking,200);
}

  /**
 * @OA\Patch(
 *     path="/bookings/{id}/status",
 *     summary="Update booking status",
 *     description="Updates the status of a booking and sends an email notification to the user with the payment link if accepted.",
 *     operationId="updateBookingStatus",
 *     tags={"Bookings"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the booking to update",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", enum={"accepted", "rejected"}, description="New status of the booking"),
 *             @OA\Property(property="notes", type="string", nullable=true, description="Optional notes for the booking")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Booking status updated and email sent",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Booking status updated and email sent")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Invalid payload")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Booking not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Booking not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Failed to send email")
 *         )
 *     ),
 * )
 */
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

        $paymentLink = '';
        if ($booking->status === 'accepted') {
            $paymentLink = 'https://buy.stripe.com/test_8wMeVv1SFfYvgb6289';
        }

        // Notify the user via email
        $user = $booking->user;
        $messageContent = ($booking->status === 'accepted')
            ? "Your booking has been accepted. Below are the details."
            : "Your booking has been rejected. Please see alternative dates.";

        try {
            // Pass the payment link to the Mailable
            Mail::to($user->email)->send(new BookingStatusMail($booking, $messageContent, $paymentLink));
            Log::info('Mail sent successfully to: ' . $user->email);
        } catch (\Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage());
        }


        $booking->email_sent = true;
        $booking->save();

        return response()->json(['message' => 'Booking status updated and email sent'], 200);
    }

      /**
     * @OA\Get(
     *     path="/bookings/my",
     *     summary="Get bookings of the authenticated user",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of user bookings",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Booking"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No bookings found"
     *     )
     * )
     */
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

   /**
     * @OA\Delete(
     *     path="/bookings/{id}",
     *     summary="Delete a booking by ID",
     *     tags={"Bookings"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This booking request has been deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
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

      /**
     * @OA\Patch(
     *     path="/bookings/{id}",
     *     summary="User can update his booking details",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"date", "time", "total_person"},
     *             @OA\Property(property="date", type="string", format="date", example="2024-10-15"),
     *             @OA\Property(property="time", type="string", example="12:00 PM"),
     *             @OA\Property(property="total_person", type="integer", example=2),
     *             @OA\Property(property="notes", type="string", example="Celebrating a birthday")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Booking")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
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





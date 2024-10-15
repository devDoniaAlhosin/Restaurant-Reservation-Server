<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\Booking;
use Carbon\Carbon;
use Stripe\Webhook;
class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // Replace with your endpoint's secret
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event['type']) {
            case 'checkout.session.completed':
                $session = $event['data']['object'];

                // Fetch booking_id and user_id from metadata
                $bookingId = $session['metadata']['booking_id'] ?? null;
                $userId = $session['metadata']['user_id'] ?? null;

                if ($bookingId && $userId) {
                    // Define the amount
                    $amount = 500; // Adjust according to your logic

                    // Create a new payment record
                    Payment::create([
                        'booking_id' => $bookingId,
                        'user_id' => $userId,
                        'amount' => $amount,
                        'payment_method' => 'stripe',
                        'payment_status' => 'completed',
                        'payment_date' => now(),
                    ]);
                } else {
                    // Handle missing booking or user ID
                    return response()->json(['error' => 'Booking or user ID not found'], 400);
                }
                break;
            // Handle other event types as needed
        }

        return response()->json(['status' => 'success'], 200);
    }

}
    // public function handleStripeWebhook(Request $request)
    // {
    //     $payload = $request->getContent();
    //     $event = null;

    //     try {
    //         // Verify the event with Stripe
    //         $event = \Stripe\Webhook::constructEvent(
    //             $payload,
    //             $request->header('Stripe-Signature'),
    //             config('services.stripe.secret')
    //         );
    //     } catch (\UnexpectedValueException $e) {
    //         // Invalid payload
    //         return response()->json(['error' => 'Invalid payload'], 400);
    //     } catch (\Stripe\Exception\SignatureVerificationException $e) {
    //         // Invalid signature
    //         return response()->json(['error' => 'Invalid signature'], 400);
    //     }

    //     // Handle the checkout.session.completed event
    //     if ($event->type === 'checkout.session.completed') {
    //         $session = $event->data->object;

    //         // Extract necessary data
    //         $bookingId = $session->metadata->booking_id; // Ensure you set this when creating the link
    //         $userId = $session->metadata->user_id; // Ensure you set this when creating the link
    //         $amount = 500; // Constant amount

    //         // Create a new payment record
    //         Payment::create([
    //             'booking_id' => $bookingId,
    //             'user_id' => $userId,
    //             'amount' => $amount,
    //             'payment_method' => 'stripe',
    //             'payment_status' => 'completed',
    //             'payment_date' => now(),
    //         ]);
    //     }

    //     return response()->json(['status' => 'success'], 200);
    // }



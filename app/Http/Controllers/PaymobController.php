<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class PaymobController extends Controller
{
      // Step 1: Authenticate with Paymob (Retrieve Auth Token)
      public function authenticate()
      {
        Log::info('Paymob Authentication Request', ['api_key' => env('PAYMOB_API_KEY')]);
          $response = Http::post(env('PAYMOB_API_URL') . '/auth/tokens', [
              'api_key' => env('PAYMOB_API_KEY'),
          ]);


          if ($response->successful()) {
              return $response->json()['token'];
          }

          Log::error('Payment Key Generation Failed', [
              'response' => $response->json(),
              'status' => $response->status(),
          ]);

          return response()->json(['message' => 'Authentication Failed'], 400);
      }


// Step 2: Create an Order in Paymob
public function createOrder(Request $request, $bookingId)
{
    $booking = Booking::find($bookingId);
    if (!$booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }


    $auth_token = $this->authenticate();
    if (!$auth_token) {
        return response()->json(['message' => 'Authentication failed'], 400);
    }
    $user = $booking->user;

    $clientFirstName = $user->username;
    $clientLastName = $user->last_name ?? 'N/A';
    $clientEmail = $user->email;
    $phone = $user->phone;
    $merchOrderId = 'ORDER_' . $bookingId . '_' . time();

    // Create Order Request to Paymob
    $response = Http::post(env('PAYMOB_API_URL') . '/ecommerce/orders', [
        'auth_token' => $auth_token,
        'delivery_needed' => false,
        'amount_cents' => $request->amount * 100,  // Convert to cents
        'currency' => 'EGP',
        'merchant_order_id' => $merchOrderId,
        'items' => [
            [

                'name' => 'Booking for ' . $booking->total_person . ' persons',
                'amount_cents' => $request->amount * 100,
                'quantity' => 1,
            ]
        ],
        'shipping_data' => [
            'first_name' => $clientFirstName,
            'last_name' => $clientLastName,
            'email' => $clientEmail,
            'phone_number' => $phone,
        ],
    ]);

    if ($response->successful()) {
        $paymobOrderId = $response->json()['id'];  // Paymob order ID

        // Save the payment details in the database
        $payment = new Payment();
        $payment->paymob_order_id = $paymobOrderId;
        $payment->booking_id = $bookingId;
        $payment->user_id = auth()->id();
        $payment->payment_method = 'credit_card';
        $payment->amount = $request->amount;
        $payment->payment_date = now();
        $payment->payment_status = 'pending'; // Set initial status
        $payment->save();

        return response()->json(['message' => 'Order created successfully', 'paymob_order_id' => $paymobOrderId], 201);
    }

    if ($response->failed()) {
        return response()->json([
            'message' => 'Order Creation Failed',
            'error' => $response->json(),
        ], 400);
    }

}




      // Step 3: Create a Payment Key
      public function createPaymentKey(Request $request, $orderId)
      {
          // Authenticate and get token
          $auth_token = $this->authenticate();

          // Create Payment Key
          $response = Http::post(env('PAYMOB_API_URL') . '/acceptance/payment_keys', [
              'auth_token' => $auth_token,
              'amount_cents' => $request->amount * 100,  // Convert to cents
              'expiration' => 3600,
              'order_id' => $orderId,
              'billing_data' => [
                  'email' => $request->email,
                  'first_name' => $request->first_name,
                  'last_name' => $request->last_name,
                  'phone_number' => $request->phone_number,
                  'apartment' => 'NA',
                  'floor' => 'NA',
                  'building' => 'NA',
                  'city' => 'Cairo',
                  'country' => 'EG',
                  'postal_code' => 'NA',
                  'street' => $request->street,  // Include the street field
              ],
              'currency' => 'EGP',
              'integration_id' => env('PAYMOB_INTEGRATION_ID'),
          ]);

          // Log the response for debugging
         // Log the response for debugging
if ($response->failed()) {
    Log::error('Payment Transaction Failed', [
        'amount' => $request->amount,
        'order_id' => $orderId,
        'response' => $response->json(),
        'status' => $response->status(),
    ]);
    return response()->json(['message' => 'Payment Transaction Failed', 'error' => $response->json()], 400);
}


          return $response->json()['token'];  // Payment Key
      }



// Step 4: Handle Callback from Paymob
public function paymentCallback(Request $request)
{
    // Step 1: Verify HMAC Signature
    $hmacIsValid = $this->validateHmac($request->all());
    Log::info('Payment callback received', $request->all());

    if (!$hmacIsValid) {
        Log::error('Invalid HMAC Signature', [
            'request_data' => $request->all(),
        ]);
        return response()->json(['message' => 'Invalid HMAC Signature'], 400);
    }

    // Step 2: Find payment by Paymob order ID
    $paymobOrderId = $request->order; // Correct the key used here
    $payment = Payment::where('paymob_order_id', $paymobOrderId)->first();

    if ($payment) {
        // Step 3: Update payment status based on Paymob response
        $paymentStatus = $request->success ? 'completed' : 'failed';
        $payment->update([
            'payment_method' => 'credit_card',
            'payment_status' => $paymentStatus,
            'payment_date' => now(),
        ]);

        return response()->json(['message' => 'Payment recorded successfully'], 200);
    }

    Log::warning('Payment not found for Paymob order ID', [
        'paymob_order_id' => $paymobOrderId,
    ]);
    return response()->json(['message' => 'Payment not found'], 404);
}



// HMAC Validation
protected function validateHmac($data)
{
    $hmac_secret = env('PAYMOB_HMAC_SECRET');
    $sorted_keys = ['amount_cents', 'created_at', 'currency', 'id', 'integration_id', 'order', 'success'];
    $hash_string = '';

    // Create hash string from the sorted keys
    foreach ($sorted_keys as $key) {
        if (array_key_exists($key, $data)) {
            $hash_string .= $data[$key];
        }
    }

    // Generate HMAC
    $calculated_hmac = hash_hmac('sha512', $hash_string, $hmac_secret);

    // Log for debugging
    Log::info('HMAC Validation', [
        'calculated_hmac' => $calculated_hmac,
        'provided_hmac' => $data['hmac'],
        'hash_string' => $hash_string,
    ]);

    return $calculated_hmac === $data['hmac'];
}




}

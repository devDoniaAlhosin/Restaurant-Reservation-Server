<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobService
{
    protected $apiKey;
    protected $authUrl;
    protected $orderUrl;
    protected $paymentKeyUrl;
    protected $integrationId;

    // Define a constant for the booking amount in cents (500 EGP = 50000 cents)
    const BOOKING_AMOUNT = 50000; // Amount in cents

    public function __construct()
    {
        $this->apiKey = env('PAYMOB_API_KEY');
        $this->authUrl = env('PAYMOB_AUTH_URL');
        $this->orderUrl = env('PAYMOB_ORDER_URL');
        $this->paymentKeyUrl = env('PAYMOB_PAYMENT_KEY_URL');
        $this->integrationId = env('PAYMOB_INTEGRATION_ID');

        // Debugging
        if (is_null($this->authUrl) || is_null($this->orderUrl) || is_null($this->paymentKeyUrl)) {
            throw new \Exception('One or more Paymob URLs are not set in the .env file.');
        }
    }

    // Step 1: Authenticate with Paymob API
    public function authenticate()
    {
        $response = Http::post($this->authUrl, ['api_key' => $this->apiKey]);

        if ($response->failed()) {
            Log::error('Paymob authentication failed: ' . $response->body());
            throw new \Exception('Authentication failed: ' . $response->body());
        }

        return $response->json()['token'] ?? null;
    }

    // Step 2: Create order in Paymob
    // Step 2: Create order in Paymob
public function createOrder($authToken, $merchantOrderId, $amountCents)
{
    Log::info('Creating order in Paymob with merchant order ID: ' . $merchantOrderId);

    $response = Http::post($this->orderUrl, [
        'auth_token' => $authToken,
        'delivery_needed' => 'false',
        'amount_cents' => $amountCents,
        'currency' => 'EGP',
        'merchant_order_id' => $merchantOrderId,
        'items' => [], // Populate this if needed
    ]);

    if ($response->failed()) {
        Log::error('Failed to create order response: ' . $response->body());
        throw new \Exception('Failed to create order: ' . $response->body());
    }

    return $response->json();
}





    // Step 3: Generate payment key
    public function generatePaymentKey($authToken, $orderId, $billingData)
    {
        $response = Http::post($this->paymentKeyUrl, [
            'auth_token' => $authToken,
            'amount_cents' => self::BOOKING_AMOUNT, // Use the constant amount
            'expiration' => 3600, // 1 hour expiration
            'order_id' => $orderId,
            'billing_data' => $billingData,
            'currency' => 'EGP',
            'integration_id' => $this->integrationId,
        ]);

        if ($response->failed()) {
            Log::error('Failed to generate payment key: ' . $response->body());
            throw new \Exception('Failed to generate payment key: ' . $response->body());
        }

        return $response->json();
    }

    // Function to process payment initiation
    public function initiatePayment($booking)
    {
        // Authenticate with Paymob
        $authToken = $this->authenticate();
        if (!$authToken) {
            throw new \Exception('Authentication with Paymob failed');
        }

        // Generate a unique merchant order ID
        $merchantOrderId = uniqid("order_");

        // Create an order in Paymob using the unique order ID
        $orderResponse = $this->createOrder($authToken, $merchantOrderId, self::BOOKING_AMOUNT);

        if (!isset($orderResponse['id'])) {
            throw new \Exception('Failed to create order with Paymob');
        }


        $billingData = $this->prepareBillingData($booking);
        $paymentKeyResponse = $this->generatePaymentKey($authToken, $orderResponse['id'], $billingData);
        Log::info('Payment Key Response: ', $paymentKeyResponse);


         // Check if payment URL exists
    if (!isset($paymentKeyResponse['payment_url'])) {
        throw new \Exception('Payment URL not available: ' . json_encode($paymentKeyResponse));
    }
        // Return payment URL and ID
        return [
            'payment_url' => $paymentKeyResponse['payment_url'] ?? '', // Ensure the payment URL exists
            'payment_id' => $paymentKeyResponse['id'] ?? null,
        ];
    }


    // Prepare billing data from booking
    private function prepareBillingData($booking)
    {
        return [
            'email' => $booking->user->email,
            'phone_number' => $booking->phone,
            'first_name' => $booking->username,
            'last_name' => 'N/A',  // Placeholder value
            'street' => 'N/A',     // Placeholder value
            'building' => 'N/A',   // Placeholder value
            'floor' => 'N/A',      // Placeholder value
            'apartment' => 'N/A',  // Placeholder value
            'city' => 'N/A',       // Placeholder value
            'country' => 'N/A',    // Placeholder value
        ];
    }
}

<!-- resources/views/emails/booking_status.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Booking Status Update</title>
</head>
<body>
    <h1>Booking Status Update</h1>

    <p>Booking Details:</p>
    <ul>
        <li>Username: {{ $booking->username }}</li>
        <li>Phone: {{ $booking->phone }}</li>
        <li>Date and Time: {{ $booking->date_time }}</li>
        <li>Total Persons: {{ $booking->total_person }}</li>
        <li>Status: {{ $booking->status }}</li>
        <li>Notes: {{ $booking->notes }}</li>
    </ul>

    @if ($booking->status === 'accepted' && !empty($paymentLink))
        <p>Your booking has been accepted! Please complete the payment using the following link:</p>
        <p><a href="{{ $paymentLink }}">Complete Payment</a></p>
    @else
        <p>Your booking has been rejected. Please contact us for more details or try booking for another time.</p>
    @endif
</body>
</html>


<!-- <!DOCTYPE html>
<html>
<head>
    <title>Booking Status Update</title>
</head>
<body>
    <h1>Booking Status Update</h1>

    <p>Booking Details:</p>
    <ul>
        <li>Username: {{ $booking->username }}</li>
        <li>Phone: {{ $booking->phone }}</li>
        <li>Date and Time: {{ $booking->date_time }}</li>
        <li>Total Persons: {{ $booking->total_person }}</li>
        <li>Status: {{ $booking->status }}</li>
        <li>Notes: {{ $booking->notes }}</li>

    </ul>
</body>
</html> -->

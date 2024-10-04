<!-- resources/views/emails/booking_status.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<h1>{{ $messageContent }}</h1>
<p><strong>Booking Details:</strong></p>
<p>Date and Time: {{ $booking->date_time }}</p>
<p>Number of people: {{ $booking->total_person }}</p>
@if($booking->status === 'accepted')
    <p>Payment Details: [Include payment information here]</p>
@else
    <p>Suggested Date/Time: [Include alternative booking suggestion here]</p>
@endif
</body>
</html>


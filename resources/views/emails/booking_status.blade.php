<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #4a90e2;
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .content h2 {
            font-size: 20px;
            color: #333333;
            margin-bottom: 10px;
        }
        .content ul {
            list-style-type: none;
            padding: 0;
        }
        .content ul li {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            font-size: 16px;
        }
        .content ul li:last-child {
            border-bottom: none;
        }
        .content ul li span {
            margin-right: 10px;
        }
        .content p {
            margin: 10px 0;
            font-size: 16px;
            color: #555555;
        }
        .footer {
            background-color: #f1f1f1;
            text-align: center;
            padding: 10px;
            font-size: 14px;
            color: #777777;
        }
        a {
            color: #4a90e2;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Booking Status Update</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Booking Details:</h2>
            <ul>
                <li><span>Username:</span> <span>{{ $booking->username }}</span></li>
                <li><span>Phone:</span> <span>{{ $booking->phone }}</span></li>
                <li><span>Date and Time:</span> <span>{{ $booking->date_time }}</span></li>
                <li><span>Total Persons:</span> <span>{{ $booking->total_person }}</span></li>
                <li><span>Status:</span> <span>{{ $booking->status }}</span></li>
                <li><span>Notes:</span> <span>{{ $booking->notes }}</span></li>
            </ul>

            @if ($booking->status === 'accepted' && !empty($paymentLink))
                <p>Your booking has been accepted! Please complete the payment using the following link:</p>
                <p><a href="{{ $paymentLink }}">Complete Payment</a></p>
            @else
                <p>Your booking has been rejected. Please contact us for more details or try booking for another time.</p>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for booking with us!</p>
        </div>
    </div>
</body>
</html>

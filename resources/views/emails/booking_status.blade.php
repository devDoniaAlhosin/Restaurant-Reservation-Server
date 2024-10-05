<!-- resources/views/emails/booking_status.blade.php
<!DOCTYPE html>
<html>
<head>
    <title>Booking Status Update</title>
</head>
<body>
    <h1>Booking Status Update</h1>
    <p>{{ $messageContent }}</p>
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
</html>

-->
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
            -webkit-text-size-adjust: none;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #2d3748;
            padding: 20px;
            color: #ffffff;
            text-align: center;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 20px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.5;
            color: #4a5568;
        }
        .content h2 {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .content ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .content ul li {
            font-size: 16px;
            color: #4a5568;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
        }
        .footer {
            text-align: center;
            padding: 10px;
            font-size: 14px;
            color: #718096;
            background-color: #edf2f7;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
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
            <!-- Message Content -->
            <p>{{ $messageContent }}</p>

            <!-- Booking Details -->
            <h2>Booking Details:</h2>
            <ul>
                <li>
                    <span>Username:</span>
                    <span>{{ $booking->username }}</span>
                </li>
                <li>
                    <span>Phone:</span>
                    <span>{{ $booking->phone }}</span>
                </li>
                <li>
                    <span>Date and Time:</span>
                    <span>{{ $booking->date_time }}</span>
                </li>
                <li>
                    <span>Total Persons:</span>
                    <span>{{ $booking->total_person }}</span>
                </li>
                <li>
                    <span>Status:</span>
                    <span>{{ $booking->status }}</span>
                </li>
                <li>
                    <span>Notes:</span>
                    <span>{{ $booking->notes }}</span>
                </li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for booking with us!</p>
        </div>
    </div>
</body>
</html>





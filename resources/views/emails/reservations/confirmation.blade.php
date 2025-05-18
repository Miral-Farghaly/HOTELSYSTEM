<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reservation Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            background-color: #f8f9fa;
            margin-bottom: 30px;
        }
        .reservation-details {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reservation Confirmation</h1>
        <p>Thank you for choosing our hotel!</p>
    </div>

    <div class="reservation-details">
        <h2>Booking Details</h2>
        <p><strong>Confirmation Code:</strong> {{ $reservation->confirmation_code }}</p>
        <p><strong>Guest Name:</strong> {{ $user->name }}</p>
        <p><strong>Check-in Date:</strong> {{ $reservation->check_in->format('F j, Y') }}</p>
        <p><strong>Check-out Date:</strong> {{ $reservation->check_out->format('F j, Y') }}</p>
        <p><strong>Room Type:</strong> {{ $room->type->name }}</p>
        <p><strong>Room Number:</strong> {{ $room->number }}</p>
        <p><strong>Total Amount:</strong> ${{ number_format($reservation->total_amount, 2) }}</p>
    </div>

    <div class="qr-code">
        <p>Scan this QR code at check-in:</p>
        {!! $qrCode !!}
    </div>

    <div class="reservation-details">
        <h3>Important Information</h3>
        <ul>
            <li>Check-in time: 3:00 PM</li>
            <li>Check-out time: 11:00 AM</li>
            <li>Please present a valid ID and the credit card used for booking</li>
            <li>Free cancellation until 24 hours before check-in</li>
        </ul>
    </div>

    <div class="footer">
        <p>If you have any questions, please contact us at:</p>
        <p>Phone: +1 (555) 123-4567</p>
        <p>Email: support@hotelname.com</p>
        <p>&copy; {{ date('Y') }} Hotel Name. All rights reserved.</p>
    </div>
</body>
</html> 
<?php
require 'config.php';
include 'connect.php';

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$status = "error";
$message = "Payment verification failed.";
$txn_id = "";

if ($booking_id > 0) {

    // 1. Fetch Booking Details
    $stmt = $conn->prepare("SELECT * FROM booking WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();

    if ($booking) {
        // 2. Update Booking Status
        $update_stmt = $conn->prepare("UPDATE booking SET booking_status = 'Confirmed' WHERE id = ?");
        $update_stmt->bind_param("i", $booking_id);
        $update_stmt->execute();

        // 3. Insert into TRANSACTIONS table
        $txn_id = "TXN" . time() . rand(1000, 9999); // Generate unique Transaction ID

        $fullname     = $booking['fullname'];
        $email        = $booking['email'];
        $room_price   = $booking['room'];        // Price per night
        $paid_amount  = $booking['total_amount']; // Total amount paid

        // NOTE: Your 'room' column stores price (int). 
        // For 'room_name' in transactions (varchar), we convert it to string.
        $room_name_str = (string)$room_price;

        // SQL Query
        $txn_sql = "INSERT INTO transactions 
                    (fullname, email, room_name, room_price, room_price_currency, paid_amount, paid_amount_currency, txn_id, payment_status, created) 
                    VALUES (?, ?, ?, ?, 'INR', ?, 'INR', ?, 'Success', NOW())";

        $txn_stmt = $conn->prepare($txn_sql);

        // FIX: Corrected Type String "sssdds" (removed space)
        // s = string, d = double/integer
        // 1.fullname(s), 2.email(s), 3.room_name(s), 4.room_price(d), 5.paid_amount(d), 6.txn_id(s)
        $txn_stmt->bind_param("sssdds", $fullname, $email, $room_name_str, $room_price, $paid_amount, $txn_id);

        if ($txn_stmt->execute()) {
            $status = "success";
            $message = "Your payment was successful! Your booking is confirmed.";
        } else {
            $message = "Booking confirmed, but transaction log failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - StaySync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght+700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0a2e36;
            --secondary-color: #cfa863;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--primary-color);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .status-card {
            background: #fff;
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
        }

        .icon-success {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 20px;
        }

        .icon-fail {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 20px;
        }

        .btn-home {
            background: var(--secondary-color);
            color: var(--primary-color);
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            display: inline-block;
            margin-top: 20px;
        }

        .btn-home:hover {
            background: #fff;
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <div class="status-card">
        <?php if ($status == 'success') { ?>
            <div class="icon-success"><i class="bi bi-check-circle-fill"></i></div>
            <h2 class="mb-3">Payment Successful!</h2>
            <p class="text-muted mb-4"><?php echo $message; ?></p>
            <div class="bg-light p-3 rounded mb-3">
                <h5>Booking ID: #<?php echo $booking_id; ?></h5>
                <h6 class="text-muted mt-2">Transaction ID: <?php echo $txn_id; ?></h6>
            </div>
            <a href="userdash.php" class="btn-home">Go to Dashboard</a>
        <?php } else { ?>
            <div class="icon-fail"><i class="bi bi-x-circle-fill"></i></div>
            <h2 class="mb-3">Payment Failed</h2>
            <p class="text-muted mb-4"><?php echo $message; ?></p>
            <a href="booking.php" class="btn-home">Try Booking Again</a>
        <?php } ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.js"></script>
</body>

</html>
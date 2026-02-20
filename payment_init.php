<?php
session_start();

/* ===============================
   INCLUDE FILES
================================ */
require_once 'config.php';
include_once 'connect.php';
require_once 'vendor/autoload.php';


\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

header('Content-Type: application/json');

$jsonStr = file_get_contents('php://input');
$jsonObj = json_decode($jsonStr, true);

if (empty($jsonObj['request_type'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

/* =====================================================
   1️⃣ CREATE PAYMENT INTENT (FROM BOOKING ID)
===================================================== */
if ($jsonObj['request_type'] == 'create_payment_intent') {

    $booking_id = intval($jsonObj['booking_id']);

    $q = mysqli_query($conn, "SELECT * FROM booking WHERE id='$booking_id'");
    $booking = mysqli_fetch_assoc($q);

    if (!$booking) {
        echo json_encode(['error' => 'Booking not found']);
        exit();
    }

    $amount = $booking['total_amount'] * 100; // Stripe uses paise
    $currency = "inr";

    try {

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'payment_method_types' => ['card'],

            // ⭐ VERY IMPORTANT
            'metadata' => [
                'booking_id' => $booking_id,
                'email' => $booking['email']
            ]
        ]);

        echo json_encode([
            'id' => $paymentIntent->id,
            'clientSecret' => $paymentIntent->client_secret
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}


/* =====================================================
   2️⃣ PAYMENT SUCCESS → UPDATE BOOKING STATUS
===================================================== */ elseif ($jsonObj['request_type'] == 'payment_success') {

    $payment_intent_id = $jsonObj['payment_intent_id'];

    try {

        $paymentIntent = \Stripe\PaymentIntent::retrieve($payment_intent_id);

        if ($paymentIntent->status == 'succeeded') {

            $booking_id = $paymentIntent->metadata->booking_id;

            // ⭐ CONFIRM BOOKING
            mysqli_query(
                $conn,
                "UPDATE booking SET booking_status='Confirmed' WHERE id='$booking_id'"
            );

            echo json_encode([
                'status' => 'success',
                'booking_id' => $booking_id
            ]);
        } else {
            echo json_encode(['error' => 'Payment not completed']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

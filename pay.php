<?php
session_start();
require 'config.php';
include "connect.php";

/* ============================
   LOGIN CHECK
============================ */
if(!isset($_SESSION['email'])){
    header("location:login.php");
    exit();
}

/* ============================
   GET BOOKING ID FROM URL
============================ */
 $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if($booking_id <= 0){
    die("Invalid Booking ID. Please go back and try again.");
}

 $email = $_SESSION['email'];

/* ============================
   FETCH BOOKING DETAILS
============================ */
 $stmt = $conn->prepare("SELECT * FROM booking WHERE id=? AND email=?");
 $stmt->bind_param("is",$booking_id,$email);
 $stmt->execute();
 $result = $stmt->get_result();
 $booking = $result->fetch_assoc();

if(!$booking){
    die("Booking not found or access denied.");
}

 $amount = $booking['total_amount'];
 $customer_name = $booking['fullname'];

/* ============================
   HANDLE MANUAL PAYMENT (GPay/Cash)
============================ */
if(isset($_POST['manual_pay'])){
    $method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    // 1. Update Booking with Payment Method and Status
    $new_status = ($method == 'Cash') ? 'Pending Payment' : 'Pending';
    
    // FIX: We also update 'payment_method' column so Admin can see it
    $update = $conn->prepare("UPDATE booking SET booking_status = ?, payment_method = ?, payment_status = 'Pending' WHERE id = ?");
    $update->bind_param("ssi", $new_status, $method, $booking_id);
    $update->execute();

    // 2. Insert into transactions table
    $txn_id = "MANUAL_" . time();
    $stmt_txn = $conn->prepare("INSERT INTO transactions (fullname, email, paid_amount, paid_amount_currency, txn_id, payment_status, created) VALUES (?, ?, ?, 'INR', ?, 'Pending', NOW())");
    $stmt_txn->bind_param("ssds", $customer_name, $email, $amount, $txn_id);
    $stmt_txn->execute();

    // 3. Redirect
    $_SESSION['pay_msg'] = "Booking recorded! Please proceed with payment via $method.";
    header("Location: userdash.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Payment - StaySync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght+700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #0a2e36;
            --secondary-color: #cfa863;
            --bg-light: #f4f7f6;
        }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-light); display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        
        .payment-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .card-header-custom {
            background: var(--primary-color);
            color: #fff;
            padding: 30px;
            text-align: center;
        }
        .card-header-custom h3 { font-family: 'Playfair Display', serif; margin: 0; color: var(--secondary-color); }
        
        .order-summary {
            background: #f8f9fa;
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .form-body { padding: 30px; }
        
        .form-label { font-weight: 500; color: var(--primary-color); font-size: 0.9rem; }
        .form-control {
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 12px;
            height: auto;
        }
        .form-control:focus { border-color: var(--secondary-color); box-shadow: none; }

        /* Stripe Element Styling */
        #paymentElement { padding: 15px; border: 2px solid #eee; border-radius: 10px; background: #fff; margin-bottom: 20px; }
        
        .btn-pay {
            background: var(--secondary-color);
            color: var(--primary-color);
            border: none;
            padding: 15px;
            border-radius: 50px;
            font-weight: 700;
            text-transform: uppercase;
            width: 100%;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        .btn-pay:hover { background: var(--primary-color); color: #fff; transform: translateY(-2px); }
        .btn-pay:disabled { background: #ccc; cursor: not-allowed; transform: none; }

        /* Custom Styles for Manual Payment */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #eee;
        }
        .divider h6 {
            padding: 0 10px;
            color: #888;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        .method-select {
            background-color: #fff;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            font-weight: 600;
            padding: 12px;
            border-radius: 10px;
        }
        
        .btn-manual {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 12px;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
            transition: 0.3s;
        }
        .btn-manual:hover {
            background: var(--primary-color);
            color: #fff;
        }

        .icon-option {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #paymentResponse { margin-top: 20px; text-align: center; }
        .hidden { display: none !important; }
        .spinner-border { width: 1rem; height: 1rem; margin-right: 5px; }
    </style>
    
    <!-- Stripe.js -->
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>

<div class="payment-card">
    <div class="card-header-custom">
        <h3>Secure Payment</h3>
        <p class="mb-0 opacity-75">Complete your reservation</p>
    </div>

    <!-- Order Summary -->
    <div class="order-summary">
        <div>
            <h6 class="mb-0 text-muted">Booking ID: #<?php echo $booking['id']; ?></h6>
            <small>Room Price: ₹<?php echo $booking['room']; ?>/night</small>
        </div>
        <div class="text-end">
            <h4 class="mb-0" style="color: var(--primary-color);">₹<?php echo number_format($amount, 2); ?></h4>
            <small class="text-muted">Total Amount</small>
        </div>
    </div>

    <div class="form-body">
        <div id="paymentResponse" class="alert alert-danger hidden"></div>

        <!-- MANUAL PAYMENT FORM (UPI / CASH) -->
        <form method="POST" class="mb-4">
            <label class="form-label">Pay via UPI or Cash</label>
            <div class="input-group mb-3">
                <select name="payment_method" class="form-select method-select" required>
                    <option value="" disabled selected>Select Payment Option</option>
                    <option value="GPay">📷 Google Pay (GPay)</option>
                    <option value="PhonePe">📱 PhonePe</option>
                    <option value="Cash">💵 Cash (Pay at Hotel)</option>
                </select>
            </div>
            <button type="submit" name="manual_pay" class="btn btn-manual">
                Continue with Selected Method
            </button>
        </form>

        <div class="divider">
            <h6>Or Pay by Card</h6>
        </div>

        <!-- STRIPE CARD PAYMENT FORM -->
        <form id="paymentFrm">
            <input type="hidden" id="booking_id" value="<?php echo $booking['id']; ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($customer_name); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" value="<?php echo $_SESSION['email']; ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Card Details</label>
                <div id="paymentElement">
                    <!-- Stripe Elements will be inserted here -->
                </div>
            </div>

            <button id="submitBtn" class="btn btn-pay">
                <span id="buttonText">Pay ₹<?php echo number_format($amount, 2); ?></span>
                <span id="spinner" class="spinner-border spinner-border-sm hidden" role="status" aria-hidden="true"></span>
            </button>
        </form>
        
        <div id="frmProcess" class="text-center hidden py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3">Processing your payment... Please do not refresh.</p>
        </div>
    </div>
</div>

<!-- Custom JS -->
<script>
    // 1. Initialize Stripe
    const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
    
    const paymentFrm = document.querySelector("#paymentFrm");
    const submitBtn = document.querySelector("#submitBtn");
    const spinner = document.querySelector("#spinner");
    const buttonText = document.querySelector("#buttonText");
    const paymentElement = document.getElementById('paymentElement');
    const messageContainer = document.querySelector("#paymentResponse");

    let elements;
    let booking_id = document.getElementById('booking_id').value;

    // 2. Initialize Payment Intent on Load
    document.addEventListener('DOMContentLoaded', async () => {
        setLoading(true);
        
        try {
            const response = await fetch("payment_init.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ 
                    request_type: 'create_payment_intent',
                    booking_id: booking_id
                }),
            });
            
            const data = await response.json();
            
            if(data.error) {
                showMessage(data.error);
                setLoading(false);
                return;
            }

            elements = stripe.elements({ 
                clientSecret: data.clientSecret,
                appearance: { theme: 'stripe', rules: { '.Label': { fontWeight: 'bold', marginBottom: '5px' } } }
            });

            const paymentEl = elements.create("payment");
            paymentEl.mount("#paymentElement");
            
            setLoading(false);
        } catch (error) {
            showMessage("Failed to initialize payment. " + error.message);
            setLoading(false);
        }
    });

    // 3. Handle Form Submit
    paymentFrm.addEventListener("submit", async (e) => {
        e.preventDefault();
        setLoading(true);

        const customer_name = document.getElementById("name").value;
        const customer_email = document.getElementById("email").value;

        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: window.location.origin + window.location.pathname.replace('pay.php', '') + 'payment_status.php?booking_id=' + booking_id,
                payment_method_data: {
                    billing_details: {
                        name: customer_name,
                        email: customer_email
                    }
                }
            },
        });

        if (error) {
            if (error.type === "card_error" || error.type === "validation_error") {
                showMessage(error.message);
            } else {
                showMessage("An unexpected error occurred.");
            }
            setLoading(false);
        }
    });

    // Helpers
    function setLoading(isLoading) {
        if (isLoading) {
            submitBtn.disabled = true;
            spinner.classList.remove("hidden");
            buttonText.classList.add("hidden");
        } else {
            submitBtn.disabled = false;
            spinner.classList.add("hidden");
            buttonText.classList.remove("hidden");
        }
    }

    function showMessage(messageText) {
        messageContainer.classList.remove("hidden");
        messageContainer.textContent = messageText;
        setTimeout(function () {
            messageContainer.classList.add("hidden");
            messageContainer.textContent = "";
        }, 5000);
    }
</script>
</body>
</html>
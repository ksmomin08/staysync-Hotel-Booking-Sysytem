<?php
session_start();
include "connect.php";

 $err = "";
 $success = "";

if(!isset($_SESSION['email'])){
    header('location: login.php');
    exit();
}

if(isset($_POST['submit'])){

    $fullname  = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email     = $_SESSION['email']; // Secure: taking email from session, not form
    $phone     = mysqli_real_escape_string($conn, $_POST['phone']);
    $age       = intval($_POST['age']);
    $room      = intval($_POST['room']); // This is price (400, 700, 2000)
    $checkin   = mysqli_real_escape_string($conn, $_POST['checkin']);
    $checkout  = mysqli_real_escape_string($conn, $_POST['checkout']);
    $adult     = mysqli_real_escape_string($conn, $_POST['adult']);
    $children  = mysqli_real_escape_string($conn, $_POST['children']);
    $special   = mysqli_real_escape_string($conn, $_POST['special']);

    // ===== VALIDATION =====
    if($fullname=="" || $email=="" || $phone=="" || $age=="" || $room=="" || $checkin=="" || $checkout==""){
        $err = "All fields are required";
    }
    elseif(!preg_match("/^[a-zA-Z ]+$/", $fullname)){
        $err = "Only letters and whitespace allowed";
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $err = "Invalid email address";
    }
    elseif(!preg_match("/^[0-9]{10}$/", $phone)){
        $err = "Phone number must be 10 digits";
    }
    elseif($age < 18){
        $err = "Age must be 18 or above";
    }
    elseif(strtotime($checkout) <= strtotime($checkin)){
        $err = "Checkout must be after checkin";
    }
    else{

        // ===== AVAILABILITY CHECK =====
        $checkRoom = "SELECT * FROM booking
                      WHERE room = '$room'
                      AND checkin <= '$checkout'
                      AND checkout >= '$checkin'";

        $result = mysqli_query($conn,$checkRoom);

        if(mysqli_num_rows($result) > 0){
            $err = "Selected room is already booked for these dates ❌";
        }else{

            // ===== CALCULATE TOTAL PRICE =====
            $days = (strtotime($checkout) - strtotime($checkin)) / (60*60*24);
            $total_amount = $days * $room;

            // ===== INSERT BOOKING =====
            $sql = "INSERT INTO booking
            (fullname,email,phone,age,room,checkin,checkout,adult,children,special,total_amount)
            VALUES
            ('$fullname','$email','$phone','$age','$room','$checkin','$checkout','$adult','$children','$special','$total_amount')";

            if(mysqli_query($conn,$sql)){
                
                // ============================================================
                // FIX: Get the last inserted ID and redirect with it
                // ============================================================
                $last_id = mysqli_insert_id($conn);
                header("Location: pay.php?booking_id=" . $last_id);
                exit();

            }else{
                $err = "Booking Failed: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room - StaySync</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght+700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #0a2e36;
            --secondary-color: #cfa863;
            --bg-light: #f4f7f6;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            min-height: 100vh;
        }

        /* Navbar */
        .navbar-custom {
            background-color: var(--primary-color);
            padding: 15px 0;
        }
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #fff !important;
            font-weight: 700;
        }
        .navbar-brand span { color: var(--secondary-color); }

        /* Booking Container */
        .booking-container {
            max-width: 900px;
            margin: 50px auto;
        }

        .booking-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            position: relative;
        }

        .booking-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--secondary-color);
        }

        .card-header-custom {
            background: var(--primary-color);
            color: #fff;
            padding: 30px;
            text-align: center;
        }

        .card-header-custom h2 {
            margin: 0;
            font-family: 'Playfair Display', serif;
            letter-spacing: 1px;
        }

        .form-body {
            padding: 40px;
        }

        .section-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            color: var(--secondary-color);
            letter-spacing: 2px;
            font-weight: 700;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 12px 15px;
            height: auto;
            font-size: 0.95rem;
            transition: 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(207, 168, 99, 0.15);
        }

        .btn-book {
            background: var(--secondary-color);
            color: var(--primary-color);
            padding: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            border: none;
            border-radius: 50px;
            width: 100%;
            transition: 0.3s;
            font-size: 1rem;
        }

        .btn-book:hover {
            background: var(--primary-color);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* Price Summary Box */
        .price-summary {
            background: #f9f9f9;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            border: 1px dashed #ccc;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .price-total {
            border-top: 2px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .form-body { padding: 25px; }
            .booking-container { margin: 20px auto; }
        }
        .b-style:hover{
            background-color: #0a2e36;
            color: white;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-custom navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="home.php">Stay<span>Sync</span></a>
            <div class="d-flex align-items-center">
                <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container booking-container">
        <div class="booking-card">
            <div class="card-header-custom">
                <h2>Reserve Your Stay</h2>
                <p class="mt-2 mb-0 opacity-75">Complete the form below to check availability</p>
            </div>

            <div class="form-body">
                <form id="bookingForm" method="POST">
                    <a href="home.php" class="b-style btn btn-outline-dark btn-sm rounded-pill px-3"><i class="bi bi-arrow-left"></i> Back</a>
                    
                    <?php if($err != ""){ ?>
                    <div class="alert alert-danger text-center mt-3"><?php echo $err; ?></div>
                    <?php } ?>

                    <!-- Guest Details Section -->
                    <div class="section-title mt-3">Guest Details</div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="fullname" class="form-control" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <!-- Readonly so user doesn't change it to someone else's -->
                            <input type="email" name="email" class="form-control" value="<?php echo $_SESSION['email']; ?>"  required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control" placeholder="1234567890" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Age</label>
                            <input type="number" name="age" class="form-control" placeholder="18" required>
                        </div>
                    </div>

                    <!-- Reservation Details Section -->
                    <div class="section-title mt-4">Reservation Details</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Room Type</label>
                            <select id="roomType" name="room" class="form-select" onchange="calculatePrice()" required>
                                <option value="" disabled selected>Select Room</option>
                                <option value="400">Standard Room (₹400/night)</option>
                                <option value="700">Deluxe Room (₹700/night)</option>
                                <option value="2000">Premium Suite (₹2000/night)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Check-In</label>
                            <input type="date" id="checkIn" name="checkin" class="form-control" onchange="setMinCheckout(); calculatePrice();" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Check-Out</label>
                            <input type="date" id="checkOut" name="checkout" class="form-control" onchange="calculatePrice()" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Adults</label>
                            <select name="adult" class="form-select">
                                <option>1 Adult</option>
                                <option selected>2 Adults</option>
                                <option>3 Adults</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Children</label>
                            <select name="children" class="form-select">
                                <option>0 Children</option>
                                <option>1 Child</option>
                                <option>2 Children</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Special Requests</label>
                            <input type="text" name="special" class="form-control" placeholder="e.g., Extra bed">
                        </div>
                    </div>

                    <!-- Dynamic Price Summary -->
                    <div class="price-summary">
                        <h5 class="mb-3" style="color: var(--primary-color);">Booking Summary</h5>
                        <div class="price-row">
                            <span>Room Price:</span>
                            <span id="summaryRoom">₹0 / night</span>
                        </div>
                        <div class="price-row">
                            <span>Duration:</span>
                            <span id="summaryNights">0 night(s)</span>
                        </div>
                        <div class="price-total">
                            <span>Total Amount:</span>
                            <span id="summaryTotal">₹0.00</span>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" name="submit" class="btn btn-book">
                            Confirm Reservation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Set minimum date for Check-in to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('checkIn').setAttribute('min', today);
        document.getElementById('checkOut').setAttribute('min', today);

        // Function to ensure Check-out is after Check-in
        function setMinCheckout() {
            const checkInDate = document.getElementById('checkIn').value;
            document.getElementById('checkOut').setAttribute('min', checkInDate);
            
            // If current checkout is before new checkin, reset it
            const checkOutDate = document.getElementById('checkOut').value;
            if(checkOutDate < checkInDate) {
                document.getElementById('checkOut').value = checkInDate;
            }
            calculatePrice();
        }

        // Function to calculate price dynamically
        function calculatePrice() {
            const roomType = document.getElementById('roomType');
            const pricePerNight = parseInt(roomType.value) || 0;
            const roomName = roomType.options[roomType.selectedIndex].text.split('(')[0].trim();

            const checkInVal = document.getElementById('checkIn').value;
            const checkOutVal = document.getElementById('checkOut').value;

            let nights = 0;
            let total = 0;

            if(checkInVal && checkOutVal) {
                const date1 = new Date(checkInVal);
                const date2 = new Date(checkOutVal);
                
                // Calculate difference in days
                const diffTime = Math.abs(date2 - date1);
                nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
            }

            if(nights > 0 && pricePerNight > 0) {
                total = nights * pricePerNight;
            }

            // Update UI
            document.getElementById('summaryRoom').innerText = `₹${pricePerNight} / night`;
            document.getElementById('summaryNights').innerText = `${nights} night(s)`;
            document.getElementById('summaryTotal').innerText = `₹${total.toLocaleString('en-IN')}.00`;
        }
    </script>
</body>
</html>
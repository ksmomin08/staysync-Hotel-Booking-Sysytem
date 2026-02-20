<?php
session_start();
include "connect.php";

if (!isset($_SESSION['email'])) {
    header('location: login.php');
    exit();
}

$email = $_SESSION['email'];

$perr = "";
$psuccess = "";

/* ===============================
   GET USER DETAILS
================================ */
$userQuery = mysqli_query($conn, "SELECT * FROM user WHERE email='$email'");
$user = mysqli_fetch_assoc($userQuery) ?? [];

/* ===============================
   GET USER BOOKINGS
================================ */
$res = mysqli_query(
    $conn,
    "SELECT * FROM booking WHERE email='$email' ORDER BY id DESC"
);

/* ===============================
   UPDATE PROFILE
================================ */
if (isset($_POST['update_profile'])) {

    $fullname = trim($_POST['fullname']);

    if ($fullname == "") {
        $perr = "Full name is required";
    } elseif (!preg_match("/^[a-zA-Z ]+$/", $fullname)) {
        $perr = "Only letters and spaces allowed";
    } else {
        $stmt = $conn->prepare("UPDATE user SET username=? WHERE email=?");
        $stmt->bind_param("ss", $fullname, $email);

        if ($stmt->execute()) {
            $psuccess = "Profile Updated Successfully";
            $user['fullname'] = $fullname;
        } else {
            $perr = "Update Failed!";
        }
    }
}

/* ===============================
   FUNCTION : ROOM NAME FIX
================================ */
function getRoomName($price)
{
    if ($price == 400) return "Standard Room";
    if ($price == 700) return "Deluxe Room";
    if ($price == 2000) return "Premium Suite";
    return "Room";
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - StaySync</title>
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
            --sidebar-width: 280px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            overflow-x: hidden;
        }

        /* --- Sidebar Styling --- */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: #fff;
            box-shadow: 5px 0 20px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 30px;
            border-bottom: 1px solid #eee;
            text-align: center;
            background: linear-gradient(to bottom, rgba(207, 168, 99, 0.05), transparent);
        }

        .sidebar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 700;
        }

        .sidebar-brand span {
            color: var(--secondary-color);
        }

        .user-info {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--secondary-color);
            margin-bottom: 15px;
            background: #eee;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .user-avatar:hover {
            transform: scale(1.05);
        }

        .user-info h5 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
        }

        .user-info p {
            margin: 0;
            color: #888;
            font-size: 0.85rem;
        }

        .nav-menu {
            flex-grow: 1;
            padding: 0 20px;
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #555;
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: all 0.3s;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .nav-item-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 0;
            background: var(--secondary-color);
            transition: height 0.3s;
        }

        .nav-item-link:hover,
        .nav-item-link.active {
            background: var(--primary-color);
            color: #fff;
            transform: translateX(5px);
        }

        .nav-item-link.active::before {
            height: 100%;
        }

        .nav-item-link i {
            margin-right: 15px;
            font-size: 1.2rem;
            width: 25px;
            text-align: center;
        }

        .logout-link {
            color: #dc3545 !important;
        }

        .logout-link:hover {
            background: #dc3545 !important;
            color: #fff !important;
        }

        /* --- Main Content --- */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }

        /* Top Header */
        .top-header {
            background: #fff;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .btn-book-now {
            background: var(--secondary-color);
            color: var(--primary-color);
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-book-now:hover {
            background: var(--primary-color);
            color: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Dashboard Cards */
        .dash-card {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            height: 100%;
            transition: all 0.3s;
            border-top: 4px solid transparent;
        }

        .dash-card:hover {
            transform: translateY(-5px);
            border-top-color: var(--secondary-color);
        }

        .active-booking-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a4a52 100%);
            color: #fff;
            border-radius: 15px;
            padding: 30px;
            position: relative;
            overflow: hidden;
        }

        .active-booking-card::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .active-booking-card h4 {
            font-family: 'Playfair Display', serif;
            color: var(--secondary-color);
        }

        .booking-detail-item {
            margin-bottom: 10px;
        }

        .booking-detail-item i {
            color: var(--secondary-color);
            width: 25px;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        /* Tables */
        .history-table {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .table thead th {
            border: none;
            background: #f8f9fa;
            color: var(--primary-color);
            font-weight: 600;
            padding: 15px;
        }

        .table tbody td {
            vertical-align: middle;
            border-color: #f0f0f0;
            padding: 15px;
        }

        /* Custom Scrollbar for table */
        .table-responsive::-webkit-scrollbar {
            height: 5px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 10px;
        }

        /* Forms */
        .form-label {
            font-weight: 500;
            color: var(--primary-color);
        }

        .form-control {
            border: 2px solid #eee;
            padding: 12px;
            border-radius: 10px;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: none;
        }

        .btn-save {
            background: var(--primary-color);
            color: #fff;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }

        .btn-save:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        /* Section Toggle */
        .content-section {
            display: none;
            animation: fadeIn 0.5s;
        }

        .content-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile */
        #mobileMenuBtn {
            display: none;
            font-size: 1.5rem;
            color: var(--primary-color);
            border: none;
            background: transparent;
        }

        @media (max-width: 992px) {
            .sidebar {
                left: -280px;
            }

            .sidebar.active {
                left: 0;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.2);
                z-index: 1100;
            }

            .main-content {
                margin-left: 0;
            }

            #mobileMenuBtn {
                display: block;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="home.php" class="sidebar-brand">Stay<span>Sync</span></a>
            <small class="d-block text-muted mt-1">Customer Panel</small>
        </div>

        <div class="user-info">
            <img src="https://placehold.co/150x150/0a2e36/fff?text=<?php echo strtoupper(substr($user['fullname'] ?? 'U', 0, 1)); ?>" alt="User" class="user-avatar">
            <h5><?php echo $user['fullname'] ?? 'User'; ?></h5>
            <p><?php echo $user['email'] ?? ''; ?></p>
        </div>

        <nav class="nav-menu">
            <a href="#" class="nav-item-link active" onclick="showSection('overview', this)">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
            <a href="#" class="nav-item-link" onclick="showSection('bookings', this)">
                <i class="bi bi-calendar-check"></i> My Bookings
            </a>
            <a href="#" class="nav-item-link" onclick="showSection('profile', this)">
                <i class="bi bi-person-lines-fill"></i> My Profile
            </a>
            <div class="mt-auto p-3">
                <a href="logout.php" class="nav-item-link logout-link">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Top Header -->
        <header class="top-header">
            <div class="d-flex align-items-center">
                <button id="mobileMenuBtn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
                <h4 class="mb-0 ms-3 fw-bold">Welcome, <?php echo $user['fullname'] ?? 'Guest'; ?>!</h4>
            </div>
            <div class="d-flex gap-2">
                <a href="home.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-house"></i> Home
                </a>
                <a href="booking.php" class="btn btn-book-now">
                    <i class="bi bi-plus-circle me-2"></i>Book Room
                </a>
            </div>
        </header>

        <!-- Content Wrapper -->
        <div class="container-fluid p-4">

            <!-- Alert Messages -->
            <?php if ($perr != "") { ?>
                <div class="alert alert-danger alert-dismissible fade show"><?php echo $perr; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php } ?>
            <?php if ($psuccess != "") { ?>
                <div class="alert alert-success alert-dismissible fade show"><?php echo $psuccess; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php } ?>

            <!-- 1. Overview Section -->
            <div id="overview" class="content-section active">
                <div class="row">
                    <!-- Stats -->
                    <div class="col-lg-4 mb-4">
                        <div class="dash-card text-center p-4">
                            <i class="bi bi-calendar-check fs-1 text-primary mb-2"></i>
                            <h3 class="fw-bold mb-0"><?php echo mysqli_num_rows($res); ?></h3>
                            <small class="text-muted">Total Bookings</small>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="dash-card text-center p-4">
                            <i class="bi bi-star fs-1 text-warning mb-2"></i>
                            <h3 class="fw-bold mb-0">Gold</h3>
                            <small class="text-muted">Member Status</small>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="dash-card text-center p-4">
                            <i class="bi bi-gift fs-1 text-success mb-2"></i>
                            <h3 class="fw-bold mb-0">10%</h3>
                            <small class="text-muted">Next Discount</small>
                        </div>
                    </div>
                </div>

                <h6 class="text-muted mb-3 text-uppercase" style="letter-spacing: 1px; font-size: 0.8rem;">Quick Actions</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="dash-card d-flex align-items-center">
                            <i class="bi bi-people fs-1 me-3" style="color: var(--secondary-color)"></i>
                            <div>
                                <h5>Planning a trip?</h5>
                                <a href="booking.php" class="text-decoration-none" style="color: var(--primary-color)">Book your next stay <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <div class="dash-card d-flex align-items-center">
                            <i class="bi bi-pencil-square fs-1 me-3" style="color: var(--secondary-color)"></i>
                            <div>
                                <h5>Update Info</h5>
                                <a href="#" onclick="showSection('profile', document.querySelector('.nav-item-link:nth-child(3)'))" class="text-decoration-none" style="color: var(--primary-color)">Edit your profile <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Bookings History Section -->
            <div id="bookings" class="content-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Booking History</h5>
                    <button class="btn btn-sm btn-outline-dark"><i class="bi bi-download me-1"></i> Export</button>
                </div>
                <div class="history-table table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Room</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Guests</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Reset pointer to loop through
                            if (mysqli_num_rows($res) > 0) {
                                mysqli_data_seek($res, 0);
                                while ($row = mysqli_fetch_assoc($res)) {
                            ?>
                                    <tr>
                                        <td><strong>#STY<?php echo $row['id']; ?></strong></td>
                                        <td>
                                            <?php echo getRoomName($row['room']); ?><br>
                                            <small class="text-muted"><?php echo $row['fullname']; ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($row['checkin'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['checkout'])); ?></td>
                                        <td><?php echo $row['adult'] . '  ' . $row['children'] . ''; ?></td>
                                        <td><span class="badge <?php echo ($row['booking_status'] == 'Pending') ? 'bg-warning' : 'bg-success'; ?>"><?php echo $row['booking_status']; ?></span>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bi bi-calendar-x fs-1 text-muted"></i>
                                        <p class="mt-2">No bookings found.</p>
                                        <a href="booking.php" class="btn btn-sm btn-book-now">Book Now</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Profile Section -->
            <div id="profile" class="content-section">
                <h5 class="fw-bold mb-4">My Profile</h5>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="dash-card">
                            <h6 class="fw-bold mb-3">Personal Information</h6>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="fullname" class="form-control" value="<?php echo $user['fullname'] ?? ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" value="<?php echo $user['email'] ?? ''; ?>" readonly disabled>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-save">Save Changes</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 mt-4 mt-lg-0">
                        <div class="dash-card">
                            <h6 class="fw-bold mb-3">Security</h6>
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" placeholder="********">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" placeholder="********">
                                </div>
                                <button type="submit" class="btn btn-outline-dark w-100">Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle Sidebar Mobile
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        // Switch Sections
        function showSection(sectionId, element) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(sec => {
                sec.classList.remove('active');
            });

            // Show current
            document.getElementById(sectionId).classList.add('active');

            // Update Menu Active State
            document.querySelectorAll('.nav-item-link').forEach(item => {
                item.classList.remove('active');
            });
            element.classList.add('active');

            // Close sidebar on mobile
            if (window.innerWidth <= 992) {
                document.getElementById('sidebar').classList.remove('active');
            }
        }
    </script>
</body>

</html>
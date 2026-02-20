<?php
session_start();
include "connect.php";

$err = "";
$success = "";

// ================== SIGNUP ==================
if (isset($_POST['signup'])) {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = trim($_POST['password']);

    if ($username == "" || $email == "" || $phone == "" || $password == "") {
        $err = "All field are required";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $username)) {
        $err = "Only letters and white space allowed";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid Email format";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $err = "Phone number must be 10 digits";
    } elseif (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[\W]/', $password)
    ) {
        $err = "Password must contain Uppercase, Lowercase, Number & Special Character";
    }

    if (empty($err)) {

        $stmt = $conn->prepare("SELECT id FROM user WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $err = "Email already exists, please login";
        } else {

            $sql = "INSERT INTO user(username,email,phone,password)
VALUES('$username','$email','$phone','$password')";

            if (mysqli_query($conn, $sql)) {
                $success = "Signup Successful! Please Login.";
            } else {
                $err = "Signup failed!";
            }
        }
    }
}

// ================== LOGIN ==================
if (isset($_POST['login'])) {

    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email == "" || $password == "") {
        $err = "All fields are required";
    } else {

        $sql = "SELECT * FROM user WHERE email='$email' AND password='$password'";
        $res = mysqli_query($conn, $sql);

        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            $_SESSION['email'] = $row['email'];
            header("location: home.php");
            exit();
        } else {
            $err = "Invalid Email or Password";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StaySync - Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #0a2e36;
            /* Deep Teal */
            --secondary-color: #cfa863;
            /* Muted Gold */
            --bg-gradient: linear-gradient(135deg, #0a2e36 0%, #1a4a52 100%);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-gradient);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* Decorative background circles */
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            z-index: 0;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            top: -100px;
            left: -100px;
        }

        .shape-2 {
            width: 600px;
            height: 600px;
            bottom: -200px;
            right: -100px;
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            border-top: 5px solid var(--secondary-color);
        }

        /* Back Button */
        .btn-back {
            position: absolute;
            top: 20px;
            left: 20px;
            background: transparent;
            border: none;
            color: #666;
            font-weight: 500;
            transition: 0.3s;
            padding: 0;
        }

        .btn-back:hover {
            color: var(--primary-color);
        }

        /* Logo */
        .logo-text {
            text-align: center;
            margin-bottom: 30px;
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--primary-color);
            font-weight: 700;
        }

        .logo-text span {
            color: var(--secondary-color);
        }

        /* Switch Box Styling */
        .switch-box {
            position: relative;
            background: #f4f4f4;
            border-radius: 50px;
            display: flex;
            margin-bottom: 30px;
            padding: 5px;
            height: 50px;
        }

        .switch-btn {
            position: absolute;
            width: 50%;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            left: 5px;
            /* Default to first tab */
            top: 5px;
            z-index: 0;
        }

        .tab {
            flex: 1;
            border: none;
            background: transparent;
            text-transform: uppercase;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 1px;
            color: #888;
            cursor: pointer;
            z-index: 1;
            transition: color 0.3s;
        }

        /* Active state for text */
        .tab.active {
            color: #fff;
        }

        /* Form Styling */
        .form-control {
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: 0.3s;
            background: #fafafa;
        }

        .form-control:focus {
            background: #fff;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(207, 168, 99, 0.2);
        }

        .btn-submit {
            background: var(--primary-color);
            border: none;
            color: #fff;
            padding: 12px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 20px;
            transition: 0.3s;
        }

        .btn-submit:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(207, 168, 99, 0.3);
        }

        /* Fade animation for forms */
        #signupForm,
        #loginForm {
            animation: fadeIn 0.4s ease;
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

        /* Responsive Adjustments */
        @media (max-width: 500px) {
            .login-card {
                padding: 30px 20px;
            }

            .tab {
                font-size: 0.75rem;
            }
        }
    </style>
</head>

<body>

    <!-- Background Decorations -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>

    <div class="login-container">
        <div class="login-card">

            <!-- Back Button -->
            <a href="home.php" class="btn-back">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>

            <!-- Logo -->
            <div class="logo-text">
                Stay<span>Sync</span>
            </div>

            <!-- Switcher Tabs -->
            <div class="switch-box">
                <div class="switch-btn"></div>
                <button id="signupTab" class="tab active">Sign Up</button>
                <button id="loginTab" class="tab">Login</button>
            </div>
            <?php if ($err != "") { ?>
                <div class="alert alert-danger text-center"><?php echo $err ?></div>
            <?php } ?>

            <?php if ($success != "") { ?>
                <div class="alert alert-success text-center"><?php echo $success ?></div>
            <?php } ?>

            <!-- Signup Form -->
            <form id="signupForm" action="" method="POST">
                <div class="input-group mb-3">
                    <span class="input-group-text border-0 bg-transparent"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Full Name" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text border-0 bg-transparent"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text border-0 bg-transparent"><i class="bi bi-phone"></i></span>
                    <input type="tel" name="phone" class="form-control" placeholder="Mobile Number" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text border-0 bg-transparent"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Create Password" required>
                </div>
                <button type="submit" name="signup" class="btn btn-submit w-100">Create Account</button>
            </form>

            <!-- Login Form -->
            <form id="loginForm" style="display:none;" action="" method="POST">
                <div class="input-group mb-3">
                    <span class="input-group-text border-0 bg-transparent"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text border-0 bg-transparent"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="d-flex justify-content-end mb-3">
                    <a href="#" style="color: var(--secondary-color); font-size: 0.85rem; text-decoration: none;">Forgot Password?</a>
                </div>
                <button type="submit" name="login" class="btn btn-submit w-100">Login</button>
            </form>

        </div>
    </div>

    <!-- jQuery & Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {

            // Function to switch tabs
            function switchTab(tabName) {
                if (tabName === 'login') {
                    $(".switch-btn").css("left", "calc(50% - 0px)"); // Move button to right
                    $("#signupForm").hide();
                    $("#loginForm").fadeIn();
                    $(".tab").removeClass("active");
                    $("#loginTab").addClass("active");
                } else {
                    $(".switch-btn").css("left", "5px"); // Move button to left
                    $("#loginForm").hide();
                    $("#signupForm").fadeIn();
                    $(".tab").removeClass("active");
                    $("#signupTab").addClass("active");
                }
            }

            $("#loginTab").click(function() {
                switchTab('login');
            });

            $("#signupTab").click(function() {
                switchTab('signup');
            });

        });
        $(document).ready(function() {
            $("input[name='password']").on("focus", function() {
                $(this).attr("type", "text");
            });
            $("input[name='password']").on("blur", function() {
                $(this).attr("type", "password");
            });
        });
    </script>
</body>

</html>
<?php
session_start();
include "connect.php";

$message = "";
$err = "";
$success = "";

// Floating booking form

if (isset($_POST['search'])) {

    $checkin  = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $adult    = $_POST['adult'];
    $children = $_POST['children'];

    if ($checkin == "" || $checkout == "") {
        $err = "Please select dates";
    } else {

        $sql = "SELECT * FROM rooms
                WHERE id NOT IN (
                    SELECT room FROM booking
                    WHERE checkin <= '$checkout'
                    AND checkout >= '$checkin'
                )";

        $res = mysqli_query($conn, $sql);

        if (mysqli_num_rows($res) > 0) {
            $success = "Available rooms ✅ Now Book Your Room";
        } else {
            $err = "No available rooms ❌";
        }
    }
}


/* ==========================
   HANDLE REVIEW SUBMISSION
========================== */
if (isset($_POST['submit_review'])) {

    $name    = trim($_POST['name']);
    $room    = $_POST['room_type'];
    $rating  = $_POST['rating'];
    $comment = trim($_POST['comment']);

    // Validation
    if (empty($name) || empty($rating) || empty($comment)) {

        $_SESSION['review_msg'] =
            "<div class='alert alert-danger'>Please fill all required fields.</div>";

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $stmt = $conn->prepare(
        "INSERT INTO reviews (user_name, room_type, rating, comment)
     VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param("ssis", $name, $room, $rating, $comment);

    if ($stmt->execute()) {

        // ⭐ SAVE MESSAGE IN SESSION
        $_SESSION['review_msg'] =
            "<div class='alert alert-success'>Thank you for your feedback!</div>";

        // ⭐ REDIRECT (MOST IMPORTANT FIX)
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {

        $_SESSION['review_msg'] =
            "<div class='alert alert-danger'>Error submitting review.</div>";

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

/* ==========================
   FETCH REVIEWS
========================== */
$reviews_result = $conn->query(
    "SELECT * FROM reviews WHERE status='approved' ORDER BY created_at DESC"
);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StaySync - Premium Hotel Booking</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="style.css">


    <!--use tripe payment intragation-->
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Stay<span>Sync</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#rooms">Rooms</a></li>
                    <li class="nav-item"><a class="nav-link" href="#facilities">Facilities</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="userdash.php">Profile</a></li>
                    <li class="nav-item ms-lg-3">
                        <a href="login.php" class="btn btn-custom">Login / Sign Up</a>
                    </li>
                    <div class="d-flex align-items-center">
                        <a href="logout.php" class="btn btn-outline-light btn-md rounded-pill px-3">Logout</a>
                    </div>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Slider -->
    <section id="home">
        <div class="swiper hero-swiper">
            <div class="swiper-wrapper">
                <!-- Using generic placeholders for demo -->
                <div class="swiper-slide"><img src="stay.jpg " alt="Slide 1"></div>
                <div class="swiper-slide"><img src="https://placehold.co/1920x1080/1a2530/ffffff?text=Welcome+to+StaySync" alt="Slide 2"></div>
                <div class="swiper-slide"><img src="https://placehold.co/1920x1080/3a2530/ffffff?text=Experience+Luxury" alt="Slide 3"></div>
                <div class="swiper-slide"><img src="https://placehold.co/1920x1080/0a3540/ffffff?text=Relax+in+Comfort" alt="Slide 4"></div>
            </div>
            <!-- Add Pagination/Navigation if desired -->
        </div>
        <!-- Hero Overlay Content -->
        <div class="hero-content">
            <p>WELCOME TO STAYSYNC</p>
            <h1>Experience The Best Hospitality</h1>
            <a href="#rooms" class="btn btn-custom" style="padding: 12px 30px;">Explore Rooms</a>
        </div>

        <!-- Floating Booking Form -->
        <div class="booking-form-container">
            <div class="booking-card">
                <h4><i class="bi bi-calendar-check me-2"></i>Check Availability</h4>
                <?php if ($err != "") { ?>
                    <div class="alert alert-danger"><?php echo $err ?></div>
                <?php } ?>

                <?php if ($success != "") { ?>
                    <div class="alert alert-success"><?php echo $success ?></div>
                <?php } ?>

                <form method="POST">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">Check-In</label>
                            <input type="date" name="checkin" class="form-control">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">Check-Out</label>
                            <input type="date" name="checkout" class="form-control">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Adults</label>
                            <select name="adult" class="form-select">
                                <option>1 Adult</option>
                                <option>2 Adults</option>
                                <option>3 Adults</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Children</label>
                            <select name="children" class="form-select">
                                <option>0 Child</option>
                                <option>1 Child</option>
                                <option>2 Children</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-12">
                            <button type="submit" name="search" class="btn btn-book">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Rooms Section -->
    <section id="rooms" class="section-padding" style="background-color: var(--bg-light);">
        <div class="container">
            <div class="section-title">
                <h2>Our Rooms</h2>
                <div class="line"></div>
            </div>

            <div class="row">
                <!-- Room 1 -->
                <div class="col-lg-4 col-md-6">
                    <div class="card room-card">
                        <div class="card-img-top position-relative overflow-hidden">
                            <img src="simg.jpg" alt="Room" class="w-100">
                            <div class="card-img-overlay">₹400/night</div>
                        </div>
                        <div class="card-body">
                            <h5>Standard Room</h5>
                            <p class="text-muted small">Perfect for solo travelers.</p>
                            <div class="mb-3">
                                <span class="badge badge-facility"><i class="bi bi-people"></i> 1 Guest</span>
                                <span class="badge badge-facility"><i class="bi bi-wifi"></i> WiFi</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div class="text-warning">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star"></i>
                                    <i class="bi bi-star"></i>
                                </div>
                                <a href="booking.php" class="btn btn-sm btn-outline-dark">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room 2 -->
                <div class="col-lg-4 col-md-6">
                    <div class="card room-card">
                        <div class="card-img-top position-relative overflow-hidden">
                            <img src="dimg.jpg" alt="Room" class="w-100">
                            <div class="card-img-overlay">₹700/night</div>
                        </div>
                        <div class="card-body">
                            <h5>Deluxe Room</h5>
                            <p class="text-muted small">Comfort for couples.</p>
                            <div class="mb-3">
                                <span class="badge badge-facility"><i class="bi bi-people"></i> 2 Guests</span>
                                <span class="badge badge-facility"><i class="bi bi-snow"></i> A.C</span>
                                <span class="badge badge-facility"><i class="bi bi-tv"></i> TV</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div class="text-warning">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star"></i>
                                </div>
                                <a href="booking.php" class="btn btn-sm btn-outline-dark">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room 3 -->
                <div class="col-lg-4 col-md-6">
                    <div class="card room-card">
                        <div class="card-img-top position-relative overflow-hidden">
                            <img src="pimg.jpg" alt="Room" class="w-100">
                            <div class="card-img-overlay">₹2000/night</div>
                        </div>
                        <div class="card-body">
                            <h5>Premium Suite</h5>
                            <p class="text-muted small">Luxury for the family.</p>
                            <div class="mb-3">
                                <span class="badge badge-facility"><i class="bi bi-people"></i> 4 Guests</span>
                                <span class="badge badge-facility"><i class="bi bi-water"></i> Pool</span>
                                <span class="badge badge-facility"><i class="bi bi-cup-hot"></i> Breakfast</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div class="text-warning">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <a href="booking.php" class="btn btn-sm btn-outline-dark">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Facilities Section -->
    <section id="facilities" class="section-padding">
        <div class="container">
            <div class="section-title">
                <h2>Hotel Facilities</h2>
                <div class="line"></div>
                <p class="text-muted mt-3">We provide top-notch amenities to make your stay memorable.</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="facility-box">
                        <i class="bi bi-wifi"></i>
                        <h5>High Speed Wifi</h5>
                        <p class="text-muted small">Stay connected with our blazing fast internet available in all areas.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="facility-box">
                        <i class="bi bi-egg-fried"></i>
                        <h5>Restaurant</h5>
                        <p class="text-muted small">Enjoy multi-cuisine dishes prepared by our expert chefs.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="facility-box">
                        <i class="bi bi-car-front"></i>
                        <h5>Parking</h5>
                        <p class="text-muted small">Secure and spacious parking area available for all guests.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="facility-box">
                        <i class="bi bi-water"></i>
                        <h5>Swimming Pool</h5>
                        <p class="text-muted small">Take a dip in our temperature-controlled infinity pool.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="facility-box">
                        <i class="bi bi-shield-check"></i>
                        <h5>24/7 Security</h5>
                        <p class="text-muted small">Your safety is our priority with round-the-clock surveillance.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="facility-box">
                        <i class="bi bi-heart-pulse"></i>
                        <h5>Spa & Gym</h5>
                        <p class="text-muted small">Rejuvenate your body at our spa and fitness center.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Review Section -->
    <section id="reviews" class="review-section-wrapper">
        <div class="review-overlay"></div>
        <div class="container position-relative" style="z-index: 2;">

            <!-- Header -->
            <div class="section-title text-center mb-5">
                <h2 class="text-white">What Our Guests Say</h2>
                <div class="line bg-white mx-auto" style="width: 80px; height: 3px;"></div>
                <p class="text-white-50 mt-3">Real experiences from our valued guests</p>
            </div>

            <!-- Display Messages (Success/Error) -->
            <div class="row justify-content-center mb-4">
                <div class="col-lg-8 text-center">
                    <?php echo $message; ?>
                </div>
            </div>

            <!-- Swiper Slider -->
            <div class="swiper reviewSwiper">
                <div class="swiper-wrapper pb-5">
                    <?php
                    if ($reviews_result->num_rows > 0) {
                        while ($row = $reviews_result->fetch_assoc()) {
                    ?>
                            <div class="swiper-slide">
                                <div class="review-card">
                                    <div class="quote-icon"><i class="bi bi-quote"></i></div>
                                    <div class="stars mb-3">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $row['rating']
                                                ? '<i class="bi bi-star-fill"></i>'
                                                : '<i class="bi bi-star"></i>';
                                        }
                                        ?>
                                    </div>
                                    <p class="review-text">"<?php echo htmlspecialchars($row['comment']); ?>"</p>
                                    <div class="reviewer-profile mt-4">
                                        <img src="https://placehold.co/100x100/0a2e36/ffffff?text=<?php echo strtoupper(substr($row['user_name'], 0, 1)); ?>" alt="User">
                                        <div class="ms-3">
                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($row['user_name']); ?></h6>
                                            <small class="text-muted"><?php echo $row['room_type'] ? $row['room_type'] : 'Verified Guest'; ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                    } else {
                        ?>
                        <!-- Placeholder if no reviews exist yet -->
                        <div class="swiper-slide">
                            <div class="review-card text-center">
                                <h4>No Reviews Yet</h4>
                                <p class="text-muted">Be the first to share your experience!</p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>

            <!-- Write Review Button -->
            <div class="text-center mt-5">
                <button class="btn btn-write-review" data-bs-toggle="modal" data-bs-target="#reviewModal">
                    <i class="bi bi-pencil-square me-2"></i>Write a Review
                </button>
            </div>
        </div>
    </section>

    <!-- Review Modal (Pop-up Form) -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-chat-heart me-2"></i>Share Your Experience</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Type (Optional)</label>
                            <select name="room_type" class="form-select">
                                <option value="">Select Room</option>
                                <option value="Standard Room">Standard Room</option>
                                <option value="Deluxe Room">Deluxe Room</option>
                                <option value="Premium Suite">Premium Suite</option>
                            </select>
                        </div>

                        <!-- Interactive Star Rating -->
                        <div class="mb-3">
                            <label class="form-label d-block">Your Rating</label>
                            <div class="rating-input-wrapper" id="starRating">
                                <i class="bi bi-star" data-value="1"></i>
                                <i class="bi bi-star" data-value="2"></i>
                                <i class="bi bi-star" data-value="3"></i>
                                <i class="bi bi-star" data-value="4"></i>
                                <i class="bi bi-star" data-value="5"></i>
                            </div>
                            <input type="hidden" name="rating" id="ratingValue" value="0" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Your Review</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="Tell us about your stay..." required></textarea>
                        </div>

                        <button type="submit" name="submit_review" class="btn btn-dark w-100 rounded-pill py-2">
                            Submit Review
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <section id="about" class="about-section-wrapper position-relative overflow-hidden">

        <!-- Decorative Background Elements -->
        <div class="about-bg-shape shape-1"></div>
        <div class="about-bg-shape shape-2"></div>

        <div class="container">
            <div class="row align-items-center">

                <!-- Image Column -->
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="about-image-wrapper">
                        <!-- Main Image -->
                        <img src="https://placehold.co/600x600/0a2e36/ffffff?text=StaySync+Hotel" class="about-main-img" alt="About Us">

                        <!-- Floating Experience Badge -->
                        <div class="experience-badge">
                            <h3>2.5+</h3>
                            <p>Years of<br>Experience</p>
                        </div>
                    </div>
                </div>

                <!-- Content Column -->
                <div class="col-lg-6">
                    <div class="about-content">
                        <span class="section-subtitle">Our Story</span>
                        <h2 class="section-title-about">About StaySync</h2>

                        <p class="lead-text">Redefining luxury and comfort since 2010.</p>

                        <p class="description-text">
                            Located in the heart of the city, StaySync offers a sanctuary of serenity amidst the bustling urban life. With over 100 rooms ranging from deluxe suites to premium penthouses, we cater to every need. Our mission is to provide a seamless stay experience with modern amenities and traditional hospitality.
                        </p>

                        <!-- Stats Cards -->
                        <div class="row mt-5 g-3">
                            <div class="col-4">
                                <div class="stat-card-about">
                                    <i class="bi bi-house-door"></i>
                                    <h3 class="counter" data-target="100">0</h3>
                                    <p>Rooms</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card-about">
                                    <i class="bi bi-emoji-smile"></i>
                                    <h3 class="counter" data-target="500">0</h3>
                                    <p>Guests</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card-about">
                                    <i class="bi bi-people"></i>
                                    <h3 class="counter" data-target="200">0</h3>
                                    <p>Staff</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h3>Stay<span style="color:var(--secondary-color)">Sync</span></h3>
                    <p>Experience world-class hospitality with modern amenities and exceptional service. Your comfort is our priority.</p>
                    <div class="social-icons mt-3">
                        <a href="#"><i class=" bi bi-facebook me-2 fs-4"></i></a>
                        <a href="#"><i class="i-style bi bi-instagram me-2 fs-4"></i></a>
                        <a href="#"><i class="bi bi-twitter-x me-2 fs-4"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#rooms">Rooms</a></li>
                        <li><a href="#facilities">Facilities</a></li>
                        <li><a href="#about">About</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Contact Us</h5>
                    <p class="small mb-1"><i class="bi bi-geo-alt me-2"></i> 123 Hotel Street, City</p>
                    <p class="small mb-1"><i class="bi bi-telephone me-2"></i> +91 1234567890</p>
                    <p class="small"><i class="bi bi-envelope me-2"></i> info@staysync.com</p>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Newsletter</h5>
                    <p class="small">Subscribe to get special offers.</p>
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Email Address">
                        <button class="btn btn-custom" type="button">Send</button>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="mb-0">&copy; 2024 StaySync. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        var reviewSwiper = new Swiper(".reviewSwiper", {
            effect: "coverflow",
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: "auto",
            initialSlide: 1, // Start with the middle card active

            coverflowEffect: {
                rotate: 0,
                /* No rotation, keeps cards straight */
                stretch: 0,
                depth: 200,
                /* Depth of the 3D effect */
                modifier: 1,
                slideShadows: false,
                /* Clean look without shadows behind */
            },

            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
                /* Pause when user hovers */
            },

            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },

            // Responsive breakpoints
            breakpoints: {
                // when window width is >= 320px
                320: {
                    slidesPerView: 1,
                    spaceBetween: 20
                },
                // when window width is >= 768px
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30
                },
                // when window width is >= 1024px
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30
                }
            }
        });

        // Initialize Hero Swiper
        var heroSwiper = new Swiper(".hero-swiper", {
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            }
        });

        // 1. Initialize Swiper
        var reviewSwiper = new Swiper(".reviewSwiper", {
            effect: "coverflow",
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: "auto",
            initialSlide: 0, // Start at first slide since data is dynamic

            coverflowEffect: {
                rotate: 0,
                stretch: 0,
                depth: 200,
                modifier: 1,
                slideShadows: false,
            },
            loop: true, // Enable loop for better UX
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                320: {
                    slidesPerView: 1
                },
                768: {
                    slidesPerView: 2
                },
                1024: {
                    slidesPerView: 3
                }
            }
        });

        // Navbar background change on scroll
        window.addEventListener('scroll', function() {
            var navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.backgroundColor = 'rgba(10, 46, 54, 0.95) !important';
                navbar.style.padding = '10px 0';
                navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
            } else {
                navbar.style.padding = '15px 0';
                navbar.style.boxShadow = 'none';
            }
        });
        // 2. Interactive Star Rating Logic
        const stars = document.querySelectorAll('#starRating i');
        const ratingInput = document.getElementById('ratingValue');

        stars.forEach(star => {
            // Click Event
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                ratingInput.value = value; // Set hidden input value

                // Visual Update
                stars.forEach(s => {
                    if (s.getAttribute('data-value') <= value) {
                        s.classList.remove('bi-star');
                        s.classList.add('bi-star-fill', 'active');
                    } else {
                        s.classList.remove('bi-star-fill', 'active');
                        s.classList.add('bi-star');
                    }
                });
            });

            // Hover Effect
            star.addEventListener('mouseover', function() {
                const value = this.getAttribute('data-value');
                stars.forEach(s => {
                    if (s.getAttribute('data-value') <= value) {
                        s.classList.add('text-warning'); // Simple hover color
                    } else {
                        s.classList.remove('text-warning');
                    }
                });
            });

            star.addEventListener('mouseout', function() {
                stars.forEach(s => s.classList.remove('text-warning'));
            });
        });
        // Intersection Observer for triggering animation when section is in view
        const aboutSection = document.querySelector('#about');
        const counters = document.querySelectorAll('.counter');
        let hasAnimated = false;

        const animateCounters = () => {
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const increment = target / 100; // Speed of count

                const updateCount = () => {
                    const current = +counter.innerText;
                    if (current < target) {
                        counter.innerText = Math.ceil(current + increment);
                        setTimeout(updateCount, 20); // Timing
                    } else {
                        counter.innerText = target + '+';
                    }
                };
                updateCount();
            });
        };

        // Only animate once when scrolled into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !hasAnimated) {
                    animateCounters();
                    hasAnimated = true;
                }
            });
        }, {
            threshold: 0.5
        });



        observer.observe(aboutSection);
    </script>
</body>

</html>
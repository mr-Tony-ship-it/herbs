<?php
session_start();
include '../includes/db.php'; // Ensure this is your correct path
include '../includes/sendOtp.php'; // Your OTP sending function

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $otp = rand(100000, 999999); // Generate a 6-digit OTP

    // Validate email format
    $is_email = filter_var($email, FILTER_VALIDATE_EMAIL);
    
    // Validate phone number format
    function isValidPhone($phone) {
        return preg_match('/^[0-9]{10}$/', $phone);
    }
    $is_phone = isValidPhone($phone);

    if (!$is_email || !$is_phone) {
        echo json_encode(['status' => 'error', 'message' => "Invalid format! Please enter a valid email and 10-digit phone number."]);
        exit;
    }

    // Check if the user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $email, $phone);
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => "Database query failed."]);
        exit;
    }
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => "User already exists. Please use a different email or phone number."]);
    } else {
        // New user registration
        $stmt = $conn->prepare("INSERT INTO users (email, phone, otp, otp_attempts, locked_until) VALUES (?, ?, ?, 0, NULL)");
        $stmt->bind_param("ssi", $email, $phone, $otp);
        
        if ($stmt->execute()) {
            sendOtp($email, $otp); // Send OTP
            echo json_encode(['status' => 'success', 'message' => "OTP sent! Check your email."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => " $phone  occurred while registering user."]);
        }
    }
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="../css/l_r.css">
</head>
<body>

    <!-- Navigation Bar -->
    <header>
    <nav>
        <a href="../index.php">Home</a>
        <a href="login.php">Login</a>
        <a href="../about.php">About</a>
    </nav>
</header>

    <!-- Form Container -->
    <div class="container">
        <h1>Register for Our Service</h1>

        <!-- Registration Form -->
        <form id="registrationForm">
            <input type="text" name="email" id="email" placeholder="Enter your Email" required>
            <input type="text" name="phone" id="phone" placeholder="Enter your Phone Number" required>

            <input type="submit" value="Register">
            <p id="message" class="message"></p>
        </form>

        <!-- OTP Form -->
        <form id="otpForm" class="otp-form">
            <h3>Enter OTP</h3>
            <input type="text" id="otp" name="otp" placeholder="6-digit OTP" required maxlength="6" pattern="[0-9]{6}">
            <p id="otpMessage" class="message"></p>
            <p>Didn't receive OTP? <a href="#" id="resendOtp">Resend</a></p>
        </form>
    </div>

    <footer>
        <p> <strong>T-Nets</strong>. All rights reserved for Tony</p>
    </footer>

    <script src="../js/register.js"></script>

</body>
</html>

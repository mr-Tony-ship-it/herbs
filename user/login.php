<?php
session_start();
include '../includes/db.php'; // Ensure this is your correct path
include '../includes/sendOtp.php'; // Your OTP sending function

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $otp = rand(100000, 999999); // Generate a 6-digit OTP

    // Check if the input is an email or phone number
    $is_email = filter_var($email, FILTER_VALIDATE_EMAIL);
    
    // Validate format
    if (!$is_email) {
        echo json_encode(['status' => 'error', 'message' => "Invalid format! Please enter a valid email."]);
        exit;
    }

    // Check if the user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => "Database query failed."]);
        exit;
    }
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if the account is blocked
        if ($user['blocked'] === 1) {
            echo json_encode(['status' => 'error', 'message' => "Your account is blocked. Please contact admin@herbs.com."]);
            exit;
        }
    }
    if ($result->num_rows > 0) {
        // User exists, update OTP
        $stmt = $conn->prepare("UPDATE users SET otp = ?, otp_attempts = 0 WHERE email = ? ");
        $stmt->bind_param("is", $otp, $email);
        
        if (!$stmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => "Error occurred while sending OTP."]);
            exit;
        }

        sendOtp($email, $otp); // Call to send the OTP
        echo json_encode(['status' => 'success', 'message' => "OTP sent! check your mail"]);
    } else {
        // User does not exist
        echo json_encode(['status' => 'error', 'message' => "User not found! Please register."]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="../css/l_r.css">

</head>

<body>

<header>
    <nav>
        <a href="../about.php">About</a>
        <a href="../index.php">Home</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<div class="container">
    <h1>Welcome Back!</h1>
    <form id="loginForm">
        <h3>Login</h3>
        <input type="text" name="email" id="email" placeholder="Enter Email" required>
        <input type="submit" value="Send OTP">
        <p id="message" class="message"></p>
    </form>
    

    <div id="otpForm">
        <h3>Enter OTP</h3>
        <input type="text" id="otp" name="otp" placeholder="Enter 6-digit OTP" required pattern="[0-9]{6}" maxlength="6">
        <p id="otpMessage" class="message"></p>
    </div>
</div>

<footer>
    <p>Powerded by <a href="#">T-Nets</a>. All rights reserved Tony.</p>
</footer>

<script src="../js/login.js"></script>

</body>
</html>

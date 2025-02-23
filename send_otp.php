<?php
session_start();
include 'includes/db.php'; // Include your database connection

if (isset($_POST['identifier'])) {
    $identifier = $_POST['identifier'];
    $sql = "SELECT * FROM users WHERE email = ? OR phone = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $otp = rand(100000, 999999); // Generate a random OTP

        // Store OTP in session for verification later
        $_SESSION['otp'] = $otp;
        $_SESSION['user_id'] = $user['id'];

        // Send OTP via email (for example)
        mail($user['email'], "Your OTP Code", "Your OTP code is: $otp");

        // For SMS, you might use a service like Twilio here

        // Respond back to the AJAX request
        echo "OTP sent successfully!";
    } else {
        echo "No user found with that email or phone number.";
    }
} else {
    echo "Identifier not provided.";
}
?>

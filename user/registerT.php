<?php
session_start(); // Start the session to manage user sessions
include '../includes/db.php'; // Ensure this is the correct path for your database connection
include '../includes/sendOtp.php'; // Include the OTP sending function

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json'); // Set content type for JSON response
    $email_or_phone = $_POST['email_or_phone'];
    $otp = rand(100000, 999999); // Generate a 6-digit OTP

    // Function to validate phone number format
    function isValidPhone($phone) {
        return preg_match('/^[0-9]{10}$/', $phone);
    }

    // Check if the input is an email or phone number
    $is_email = filter_var($email_or_phone, FILTER_VALIDATE_EMAIL);
    $is_phone = isValidPhone($email_or_phone);

    // Validate format
    if (!$is_email && !$is_phone) {
        echo json_encode(['status' => 'error', 'message' => "Invalid format! Please enter a valid email or 10-digit phone number."]);
        exit;
    }

    // Check if account is locked
    $stmt = $conn->prepare("SELECT locked_until FROM users WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $email_or_phone, $email_or_phone);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['locked_until'] !== null && $user['locked_until'] > time()) {
        $remaining_time = round(($user['locked_until'] - time()) / 60);
        echo json_encode(['status' => 'error', 'message' => "Account is locked. Try again after $remaining_time minutes."]);
        exit;
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $email_or_phone, $email_or_phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // New user registration
        $email = $is_email ? $email_or_phone : null;
        $phone = $is_phone ? $email_or_phone : null;

        // Insert new user into the database
        $stmt = $conn->prepare("INSERT INTO users (email, phone, otp, otp_attempts, locked_until) VALUES (?, ?, ?, 0, NULL)");
        $stmt->bind_param("ssi", $email, $phone, $otp);

        if ($stmt->execute()) {
            sendOtp($email_or_phone, $otp); // Send OTP
            echo json_encode(['status' => 'success', 'message' => "OTP sent!"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => "Error occurred while registering user."]);
        }
    } else {
        // User already exists, send OTP
        $existing_user = $result->fetch_assoc();
        $stmt = $conn->prepare("UPDATE users SET otp = ?, otp_attempts = 0 WHERE email = ? OR phone = ?");
        $stmt->bind_param("iss", $otp, $existing_user['email'], $existing_user['phone']);
        
        if ($stmt->execute()) {
            sendOtp($email_or_phone, $otp); // Send OTP
            echo json_encode(['status' => 'success', 'message' => "OTP sent!"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => "Error occurred while sending OTP."]);
        }
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <style>
        /* Basic styling for the forms */
        form, #otpForm {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        #message, #otpMessage {
            color: red;
            margin-top: 10px;
        }

        #resendOtp {
            color: #4CAF50;
            text-decoration: none;
        }

        #resendOtp:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Registration Form -->
    <form id="registrationForm">
        <h3>Register</h3>
        <input type="text" name="email_or_phone" id="email_or_phone" 
               placeholder="Enter Email or Phone" required>
        <input type="submit" value="Register">
        <p id="message"></p>
    </form>

    <!-- OTP Verification Form -->
    <div id="otpForm" style="display: none;">
        <h3>Enter OTP</h3>
        <input type="text" id="otp" name="otp" placeholder="Enter 6-digit OTP" 
               required pattern="[0-9]{6}" maxlength="6">
        <p id="otpMessage"></p>
        <p>Didn't receive OTP? <a href="#" id="resendOtp">Resend</a></p>
    </div>

    <script>
        function validateForm(input) {
            const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
            const phonePattern = /^[0-9]{10}$/;
            return emailPattern.test(input) || phonePattern.test(input);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const registrationForm = document.getElementById('registrationForm');
            const otpForm = document.getElementById('otpForm');
            const otpInput = document.getElementById('otp');
            const message = document.getElementById('message');
            const otpMessage = document.getElementById('otpMessage');
            const resendOtp = document.getElementById('resendOtp');
            let emailOrPhone;
            
            registrationForm.addEventListener('submit', function(e) {
                e.preventDefault();
                emailOrPhone = document.getElementById('email_or_phone').value;
                if (validateForm(emailOrPhone)) {
                    const formData = new FormData(this);
                    fetch('register.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            registrationForm.style.display = 'none';
                            otpForm.style.display = 'block';
                            otpMessage.textContent = data.message;
                            otpMessage.style.color = 'green';
                        } else {
                            message.textContent = data.message;
                            message.style.color = 'red';
                        }
                    })
                    .catch(error => {
                        message.textContent = "An error occurred. Please try again.";
                        message.style.color = 'red';
                        console.error('Error:', error);
                    });
                } else {
                    message.textContent = 'Please enter a valid email address or 10-digit phone number';
                    message.style.color = 'red';
                }
            });
            
            otpInput.addEventListener('input', function() {
                if (this.value.length === 6) {
                    const formData = new FormData();
                    formData.append('email_or_phone', emailOrPhone); // Use the email or phone from registration
                    formData.append('otp', this.value);
                    
                    fetch('verify_otp.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            otpMessage.textContent = data.message;
                            otpMessage.style.color = 'green';
                            window.location.href = 'index.php'; // Update with your actual dashboard path
                        } else {
                            otpMessage.textContent = data.message;
                            otpMessage.style.color = 'red';
                        }
                    })
                    .catch(error => {
                        otpMessage.textContent = "An error occurred while verifying OTP. Please try again.";
                        otpMessage.style.color = 'red';
                        console.error('Error:', error);
                    });
                }
            });

            resendOtp.addEventListener('click', function(e) {
                e.preventDefault();
                const formData = new FormData();
                formData.append('email_or_phone', emailOrPhone);
                
                fetch('register.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        otpMessage.textContent = data.message;
                        otpMessage.style.color = 'green';
                    } else {
                        otpMessage.textContent = data.message;
                        otpMessage.style.color = 'red';
                    }
                })
                .catch(error => {
                    otpMessage.textContent = "An error occurred while resending OTP. Please try again.";
                    otpMessage.style.color = 'red';
                    console.error('Error:', error);
                });
            });
        });
    </script>
</body>
</html>

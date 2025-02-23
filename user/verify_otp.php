<?php
session_start();
include '../includes/db.php';

$max_attempts = 3;
$lockout_duration = 7200; // 2 hours in seconds

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if 'email' and 'otp' are set in the POST request
    if (!isset($_POST['email']) || !isset($_POST['otp'])) {
        echo json_encode(['status' => 'error', 'message' => "Missing email or OTP"]);
        exit;
    }

    $email = $_POST['email'];
    $entered_otp = $_POST['otp'];

    // Check if account is locked
    $stmt = $conn->prepare("SELECT locked_until FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['locked_until'] > time()) {
        $remaining_time = round(($user['locked_until'] - time()) / 60);
        echo json_encode(['status' => 'error', 'message' => "Account is locked. Try again after $remaining_time minutes."]);
        exit;
    }

    // Validate OTP
    $stmt = $conn->prepare("SELECT id, role, otp, otp_attempts FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user['otp'] == $entered_otp) {
            // Correct OTP
            $stmt = $conn->prepare("UPDATE users SET otp_attempts = 0, locked_until = NULL WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();

            // Set session
            $_SESSION['user'] = $email;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            echo json_encode(['status' => 'success', 'message' => 'OTP verified successfully', 'role' => $user['role']]);
        } else {
            // Increment OTP attempts
            $stmt = $conn->prepare("UPDATE users SET otp_attempts = otp_attempts + 1 WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();

            // Check attempts
            $stmt = $conn->prepare("SELECT otp_attempts FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user['otp_attempts'] >= $max_attempts) {
                // Lock account
                $locked_until = time() + $lockout_duration;
                $stmt = $conn->prepare("UPDATE users SET locked_until = ? WHERE email = ?");
                $stmt->bind_param("is", $locked_until, $email);
                $stmt->execute();

                echo json_encode(['status' => 'error', 'message' => 'Account locked for 2 hours due to multiple failed attempts.']);
            } else {
                $remaining_attempts = $max_attempts - $user['otp_attempts'];
                echo json_encode(['status' => 'error', 'message' => "Invalid OTP. $remaining_attempts attempts remaining."]);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => "User not found!"]);
    }
}
?>

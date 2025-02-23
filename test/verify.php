<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = $_POST['otp'];
    $contact = $_SESSION['contact'];
    $_SESSION['attempts']++;

    if (isset($_SESSION['locked']) && time() < $_SESSION['locked']) {
        echo "This contact is temporarily locked. Please try again later.";
        exit;
    }

    if ($entered_otp == $_SESSION['otp']) {
        echo 'OTP verified successfully!';
    } else {
        if ($_SESSION['attempts'] > 2) {
            $_SESSION['locked'] = time() + (2 * 60 * 60); // Lock for 2 hours
            echo "You have entered incorrect OTP 3 times. Please try again after 2 hours.";
        } else {
            echo 'Incorrect OTP. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <form method="post" action="">
        <input type="text" name="otp" placeholder="Enter your OTP" required>
        <button type="submit">Verify OTP</button>
    </form>
</body>
</html>

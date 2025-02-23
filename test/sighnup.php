<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contact = $_POST['contact'];
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['contact'] = $contact;
    $_SESSION['attempts'] = 0;

    if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@example.com';
        $mail->Password = 'your-password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your-email@example.com', 'Your Name');
        $mail->addAddress($contact);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = 'Your OTP code is ' . $otp;

        if (!$mail->send()) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'OTP has been sent';
        }
    } else {
        // Send OTP via SMS using a service like Twilio
        // Example using Twilio's PHP library:
        // $sid = 'your_twilio_sid';
        // $token = 'your_twilio_token';
        // $twilio = new Client($sid, $token);
        //
        // $message = $twilio->messages
        //     ->create($contact, [
        //         'from' => 'your_twilio_phone_number',
        //         'body' => 'Your OTP code is ' . $otp
        //     ]);
        echo 'OTP sent via SMS';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <form method="post" action="">
        <input type="text" name="contact" placeholder="Enter your email or phone number" required>
        <button type="submit">Sign Up</button>
    </form>
</body>
</html>

<?php
require '../vendor/autoload.php'; // Ensure this points to the correct path

use Mailgun\Mailgun;

function sendOtp($recipient, $otp) {
    $mgClient = Mailgun::create('64187d41d8b9f02ba735c157bdf03157-f6fe91d3-7b8871ad'); // Replace with your Mailgun API Key
    $domain = "mail.herb.studio"; // Replace with your Mailgun domain

    $result = $mgClient->messages()->send($domain, [
        'from'    => 'postmaster@mail.herb.studio', // Replace with your verified Mailgun sender
        'to'      => $recipient,
        'subject' => 'Your OTP Code',
        'html'    => "<strong>Your OTP code is $otp</strong>. Please enter this code to verify your account."
    ]);

    return $result;
}
?>

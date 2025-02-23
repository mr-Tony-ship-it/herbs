<?php
function sendOtp($to, $otp) {
    $subject = "Your OTP Code";
    $message = "Your OTP code is: $otp";
    // Use mail() function to send email
    mail($to, $subject, $message);
  
}
?>

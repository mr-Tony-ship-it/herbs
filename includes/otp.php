<?php
function sendOtp($to, $otp) {
    $subject = "Your OTP Code";
    $message = "Your OTP code is: $otp";
    // Use mail() function to send email
    mail($to, $subject, $message);
  //  45893f6f14530ffa3773e302d6a9c50b-72e4a3d5-aa1491f9
}
?>

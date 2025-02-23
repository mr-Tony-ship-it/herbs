<?php
$host = 'localhost';
$user = 'root'; // Default for XAMPP
$pass = ''; // Leave blank if there's no password
$db = 'herbs_booking'; // Change to your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>

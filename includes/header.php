<!-- header.php -->
<?php ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
include 'includes/db.php'; // Database connection
session_start(); // Start the session

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Herb Haven</title>
</head>
<body>
    <header>
       
        <nav class="navbar">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="user/login.php">Login</a>
            <a href="user/register.php">Join</a>
        </nav>
    </header>

    <div class="navbar">
    <a href="#" class="logo">Herb Haven</a>
    <div class="nav-links">
        <?php if ($isLoggedIn): ?>
            <a href="index.php">Home</a>
            <a href="user/account_settings.php" class="button">Account</a>
            <a href="logout.php" class="button">Logout</a>
        <?php else: ?>
            <a href="index.php">Home</a>
            <a href="user/register.php" class="button logout-button">Join</a>
            <a href="user/login.php" class="button logout-button">Login</a>
        <?php endif; ?>
    </div>
</div>
    
</div>

<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user ID from session
$user_id = $_SESSION['user_id'];

// Fetch current user details
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($current_email);
$stmt->fetch();
$stmt->close();

// Handle email update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
    $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: account_settings.php"); // Refresh after update
    exit();
}

// Handle account deactivation
if (isset($_POST['delete'])) {
    $stmt = $conn->prepare("delete from users  WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    session_destroy();  // Destroy session after deactivation
    header("Location: login.php");  // Redirect to login page after deactivation
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            padding: 20px;
            background-color: white;
            max-width: 900px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .section-title {
            font-size: 24px;
            margin-bottom: 20px;
        }
        input[type="email"], input[type="submit"], .button {
            padding: 10px;
            width: 100%;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        input[type="email"]:focus {
            border-color: #4CAF50;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        .button:hover {
            background-color: #45a049;
        }
        .footer {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 20px;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
    </style>
</head>
<body>

<div class="navba">
    <h3 class="logo">Welcome, <?php echo htmlspecialchars($current_email); ?></h3>
    <div class="nav-links">
        <a href="../index.php" class="nav-link">Home</a>
            <a href="view_purchases.php" class="nav-link">View Purchases</a>
            <a href="cart.php" class="nav-link">View Cart</a>
            <a href="../logout.php" class="nav-link">Logout</a>
        </div>
    </div>

    <!-- Account Settings -->
    <div class="container">
        <p class="section-title">Update Your Email</p>
        <form action="account_settings.php" method="POST">
            <input type="email" name="email" placeholder="Enter current mail" value="<?php echo htmlspecialchars($current_email); ?>" required>
            <input type="submit" value="Update Email">
        </form>

        <!-- delete Account Form -->
        <form action="account_settings.php" method="POST" onsubmit="return confirm('Are you sure you want to delete your account?');">
            <input type="submit" name="delete" value="delete Account" class="button" style="background-color: red;">
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>


</body>
</html>

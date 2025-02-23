<?php
include '../includes/db.php'; // Database connection

// Fetch all users
$feedBacks = $conn->query("SELECT * FROM feedbacks");

if (!$feedBacks) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css"/>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 20px;
        }
        h2 {
            color: #2c3e50;
        }
        .user-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .user-card:hover {
            transform: scale(1.02);
        }
        .button, .navbar a {
            background: #4CAF50; /* Same background as the navbar */
            color: white;
            border: none;
            padding: 10px 15px; /* Adjusted padding for uniformity */
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            font-size: 16px; /* Ensure consistent font size */
        }
        .button-danger {
            background: #e74c3c; /* Red color for danger actions */
        }
        .status-blocked {
            color: red;
            font-weight: bold;
        }
        .status-active {
            color: green;
            font-weight: bold;
        }
        #rate{
            color:red;
        }
    </style>
</head>
<body>

<div class="navba">
    <h3 class="logo"></h3>
    <div class="nav-links">
            <a href="index.php" class="nav-link">Dashboard</a>
            <a href="add_item.php"class="nav-link">Add New Product</a>
            <a href="view_users.php" class="nav-link">View Users</a>
            <a href="../logout.php" class="nav-link">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>All Feedbacks</h2>
        <?php if ($feedBacks->num_rows > 0): ?>
            <?php while ($review = $feedBacks->fetch_assoc()): ?>
                <div class="user-card">
                    <p><label>Date: </label><?php echo htmlspecialchars($review['created_at']); ?></p>
                    <label>Rating:</label> <strong id="rate"><?php echo htmlspecialchars($review['rating']); ?></strong>
                    <p><?php echo htmlspecialchars($review['feedback']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No feedbacks.</p>
        <?php endif; ?>
    </div>

</body>
</html>

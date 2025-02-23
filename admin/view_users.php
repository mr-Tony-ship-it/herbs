<?php
include '../includes/db.php'; // Database connection

// Fetch all users
$userResult = $conn->query("SELECT * FROM users where role='user'");

if (!$userResult) {
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
    </style>
</head>
<body>

<div class="navba">
    <h3 class="logo"></h3>
    <div class="nav-links">
            <a href="index.php" class="nav-link">Dashboard</a>
            <a href="report.php" class="button">Report</a>
            <a href="add_item.php" class="nav-link">Add New Product</a>
            <a href="view_users.php" class="nav-link">View Users</a>
            <a href="../logout.php" class="nav-link">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>All Users</h2>
        <?php if ($userResult->num_rows > 0): ?>
            <?php while ($user = $userResult->fetch_assoc()): ?>
                <div class="user-card">
                    <p><label>Email: </label><?php echo htmlspecialchars($user['email']); ?></p>
                    <p><label>Phone No: </label><?php echo htmlspecialchars($user['phone']); ?></p>
                    <?php if ($user['blocked']): ?>
                        <p class="status-blocked">Status: Blocked</p>
                        <!-- Unblock User Button -->
                        <form action="unblock_user.php" method="post" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="button">Unblock User</button>
                        </form>
                    <?php else: ?>
                        <p class="status-active">Status: Active</p>
                        <!-- Block User Button -->
                        <form action="block_user.php" method="post" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="button">Block User</button>
                        </form>
                    <?php endif; ?>

                    <a href="transaction_history.php?user_id=<?php echo $user['id']; ?>" class="button">View Transactions</a>
                    <a href="carted_items.php?user_id=<?php echo $user['id']; ?>" class="button">View Carted Items</a>

                    <!-- Remove User Button -->
                    <form action="remove_user.php" method="post" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="button button-danger">Remove User</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>

</body>
</html>

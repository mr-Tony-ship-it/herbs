<?php
include '../includes/db.php'; // Database connection

// Ensure the user ID is numeric and safe to use in the query
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($user_id <= 0) {
    die("Invalid user ID.");
}

// Fetch the order history for the user, including product names, quantities, and total amounts
$query = "
    SELECT o.order_date, o.total, o.address, o.status, oi.quantity, p.name AS product_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = $user_id
    ORDER BY o.order_date DESC
";
$transactionResult = $conn->query($query);

if (!$transactionResult) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 20px;
        }
        h2 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #45a049;
        }
        .navbar {
            background: #4CAF50;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
    </style>
</head>
<body>

<div class="navbar">
        <h1>User Details</h1>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="add_item.php" class="button">Add New Product</a>
            <a href="../logout.php" class="button">Logout</a>
        </div>
    </div>

    <h2>Transaction History for User ID: <?php echo htmlspecialchars($user_id); ?></h2>
    <table>
        <tr>
            <th>Date</th>
            <th>Product</th>
            <th>Amount</th>
            <th>Quantity</th>
            <th>Shipping Address</th>
            <th>Status</th>
        </tr>
        <?php if ($transactionResult->num_rows > 0): ?>
            <?php while ($transaction = $transactionResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($transaction['order_date']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['product_name']); ?></td>
                    <td><?php echo number_format($transaction['total'], 2); ?></td>
                    <td><?php echo htmlspecialchars($transaction['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['address']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No transactions found.</td>
            </tr>
        <?php endif; ?>
    </table>

    <a href="view_users.php" class="button">Back to Users</a>
</body>
</html>

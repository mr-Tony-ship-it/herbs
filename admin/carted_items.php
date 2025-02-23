<?php
include '../includes/db.php'; // Database connection

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Fetch carted items for the user
$cartResult = $conn->query("
    SELECT c.quantity, p.name AS item_name,p.price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = $user_id
");

if (!$cartResult) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carted Items</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 20px;
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

    <h2>Carted Items for User ID: <?php echo htmlspecialchars($user_id); ?></h2>
    <table>
        <tr>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Price</th>
        </tr>
        <?php if ($cartResult->num_rows > 0): ?>
            <?php while ($item = $cartResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td><?php echo number_format($item['price'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">No items in the cart.</td>
            </tr>
        <?php endif; ?>
    </table>

    <a href="view_users.php" class="button">Back to Users</a>
</body>
</html>

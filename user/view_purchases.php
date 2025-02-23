<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Prepare the SQL statement
$stmt = $conn->prepare("
    SELECT o.id AS order_id, o.order_date, o.address AS shipping_address, o.total, o.status, 
           oi.product_id, oi.quantity, p.name AS product_name, p.price
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");

// Check if preparation was successful
if ($stmt === false) {
    echo "Error preparing the SQL query: " . $conn->error;
    exit();
}

// Bind the parameter
$stmt->bind_param("i", $user_id);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Purchases</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
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
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        .order-summary {
            font-weight: bold;
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 10px;
        }

        .order-summary span {
            display: inline-block;
            margin-right: 15px;
        }

        .order-row {
            background-color: #fafafa;
        }

        .order-status {
            font-weight: bold;
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .order-status.completed {
            background-color: #4CAF50;
        }

        .order-status.cancelled {
            background-color: #FF9800;
        }

        .order-status.failed {
            background-color: #F44336;
        }
        .order-status.pending {
            background-color: skyblue;
        }

        .button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #45a049;
        }

        .no-orders {
            font-size: 18px;
            color: #333;
        }
    </style>
</head>
<body>

<div class="navba">
    <h3 class="logo">Your Purchases</h3>
    <div class="nav-links">
        <a href="../index.php" class="nav-link">Home</a>
        <a href="account_settings.php" class="nav-link">Account Settings</a>
        <a href="cart.php" class="nav-link">View Cart</a>
        <a href="../logout.php" class="nav-link">Logout</a>
    </div>
</div>

<!-- Purchases Table -->
<div class="container">
    <h2 class="section-title">Your Order History</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <?php
            $current_order_id = null;
            while ($order = $result->fetch_assoc()) {
                // If it's a new order, display the order summary
                if ($order['order_id'] != $current_order_id) {
                    // Close previous table if needed
                    if ($current_order_id !== null) {
                        echo '</tbody></table>';
                    }

                    // Set the current order_id to the new order
                    $current_order_id = $order['order_id'];

                    // Start a new order summary and table
                    echo "<div class='order-summary'>
                            <span>Order ID: {$order['order_id']}</span>
                            <span>Date: {$order['order_date']}</span>
                            <span>Total: Rs. {$order['total']}</span>
                            <span class='order-status " . strtolower($order['status']) . "'>{$order['status']}</span>
                          </div>
                          <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Shipping Address</th>
                                </tr>
                            </thead>
                            <tbody>";
                }

                // Display order items
                echo "<tr class='order-row'>
                        <td>{$order['product_name']}</td>
                        <td>{$order['quantity']}</td>
                        <td>Rs. {$order['price']}</td>
                        <td>Rs. " . ($order['quantity'] * $order['price']) . "</td>
                        <td>{$order['shipping_address']}</td>
                      </tr>";
            }
        ?>
        </tbody>
    </table>
    </div>
    <?php else: ?>
        <p class="no-orders">You have no orders yet.</p>
    <?php endif; ?>
    
</div>

</body>
</html>

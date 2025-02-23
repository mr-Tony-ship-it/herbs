<?php
include '../includes/db.php'; // Include the database connection
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;

// If no order_id is provided, show an error
if (!$orderId) {
    echo "Invalid order ID.";
    exit();
}

// Fetch the order details from the database
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$orderResult = $stmt->get_result();
$order = $orderResult->fetch_assoc();

// If order is not found, show an error
if (!$order) {
    echo "Order not found.";
    exit();
}

// SQL query to update the order status to 'completed'
$sql = "UPDATE orders SET status='completed' WHERE id=?";

// Prepare the statement to avoid SQL injection
$stmt = $conn->prepare($sql);

// Check if prepare() was successful
if ($stmt === false) {
    die('Error preparing statement: ' . $conn->error);
}

// Bind the order_id to the statement
$stmt->bind_param("i", $orderId); // "i" means the orderId is an integer

// Execute the query
if ($stmt->execute()) {
    // Order status updated to 'completed', now clear the cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    if ($stmt === false) {
        die('Error preparing cart deletion: ' . $conn->error);
    }

    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        die('Error executing cart deletion: ' . $stmt->error);
    }

    // Fetch the order items
    $stmt = $conn->prepare("SELECT oi.*, p.name, p.price FROM order_items oi
                            JOIN products p ON oi.product_id = p.id
                            WHERE oi.order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $orderItemsResult = $stmt->get_result();

    $orderItems = [];
    while ($item = $orderItemsResult->fetch_assoc()) {
        $orderItems[] = $item;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <style>
        /* Add styles for the confirmation page */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .order-details {
            margin-bottom: 30px;
        }

        .order-details h3 {
            font-size: 20px;
            margin-bottom: 15px;
        }

        .order-details p {
            font-size: 16px;
            margin: 5px 0;
        }

        .order-items table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .order-items th, .order-items td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .back-button {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="section-title">Payment Successful!</h1>

    <div class="order-details">
        <h3>Order Details</h3>
        <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
        <p><strong>Total Amount:</strong> Rs. <?php echo number_format($order['total'], 2); ?></p>
        <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['address']) . ', ' . htmlspecialchars($order['city']) . ', ' . htmlspecialchars($order['zip']); ?></p>
        <p><strong>Payment Method:</strong> <?php echo ucfirst(htmlspecialchars($order['payment_method'])); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($order['status'])); ?></p>
    </div>

    <div class="order-items">
        <h3>Items Purchased</h3>
        <table>
            <tr>
                <th>Product Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
            <?php foreach ($orderItems as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                <td>Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
<br><br>
    <a href="../index.php" class="back-button">Go Back to Home</a>
</div>

</body>
</html>

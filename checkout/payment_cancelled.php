<?php
include '../includes/db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Ensure $orderId is defined and sanitize it
if (isset($_GET['order_id'])) {
    $orderId = intval($_GET['order_id']); // Sanitize the order ID
} else {
    die("Error: Order ID is missing.");
}

// SQL query to update the order status to 'failed'
$sql = "UPDATE orders SET status='cancelled' WHERE id=? AND user_id=?"; // Ensure the order belongs to the logged-in user

// Prepare the statement to avoid SQL injection
$stmt = $conn->prepare($sql);

// Check if prepare() was successful
if ($stmt === false) {
    // Output error if the prepare fails
    die('Error preparing statement: ' . $conn->error);  // Display the detailed MySQL error
}

// Bind the order_id and user_id to the statement
$stmt->bind_param("ii", $orderId, $userId); // Bind both order_id and user_id to ensure proper ownership check


if ($stmt->execute()) {
    // Successfully updated the order status
    // Now, let's restore the product quantities
    // First, fetch the products in the order
    $productQuery = "SELECT product_id, quantity FROM order_items WHERE order_id=?";
    $productStmt = $conn->prepare($productQuery);

    // Check if prepare() was successful for productStmt
    if ($productStmt === false) {
        die('Error preparing product query: ' . $conn->error);  // Display error if prepare fails
    }

    $productStmt->bind_param("i", $orderId);
    $productStmt->execute();
    $productResult = $productStmt->get_result();

    // Restore the quantities for each product
    while ($product = $productResult->fetch_assoc()) {
        $productId = $product['product_id'];
        $quantity = $product['quantity'];

        // Update the product quantity in the products table
        $restoreQuantityQuery = "UPDATE products SET stock_quantity = stock_quantity + ? WHERE id=?";
        $restoreStmt = $conn->prepare($restoreQuantityQuery);

        // Check if prepare() was successful for restoreStmt
        if ($restoreStmt === false) {
            die('Error preparing restore query: ' . $conn->error);  // Display error if prepare fails
        }

        $restoreStmt->bind_param("ii", $quantity, $productId);

        // Check if the restore query was successful
        if (!$restoreStmt->execute()) {
            die('Error restoring product quantity: ' . $restoreStmt->error);  // Display error if execute fails
        }

        // Close the restore statement
        $restoreStmt->close();
    }

    // Close the product statement
    $productStmt->close();

} else {
    // Error executing the query
    die('Error executing query: ' . $stmt->error);  // Display the MySQL error for debugging
}

// Close the statement
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .message-box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .message-box h3 {
            color: #ff0000;
        }
        .message-box p {
            color: #333;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="message-box">
    <h3>Payment Cancelled</h3>
    <p>Your payment for order #<?php echo htmlspecialchars($orderId); ?> has been cancelled.</p>
    <p><a href="../index.php" class="button">Go for shopping</a></p>
</div>

</body>
</html>

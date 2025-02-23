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
$sql = "UPDATE orders SET status='failed' WHERE id=? AND user_id=?"; // Ensure the order belongs to the logged-in user

// Prepare the statement to avoid SQL injection
$stmt = $conn->prepare($sql);

// Check if prepare() was successful
if ($stmt === false) {
    // Output error if the prepare fails
    die('Error preparing statement: ' . $conn->error);  // Display the detailed MySQL error
}

// Bind the order_id and user_id to the statement
$stmt->bind_param("ii", $orderId, $userId); // Bind both order_id and user_id to ensure proper ownership check

// Execute the query
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
    <title>Payment Failed</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: red;
            text-align: center;
        }

        p {
            font-size: 16px;
            margin: 15px 0;
        }

        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 12px 25px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #45a049;
        }

        .back-button {
            display: inline-block;
            background-color: #f44336;
            color: white;
            padding: 12px 25px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            margin-top: 20px;
        }

        .back-button:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Payment Failed</h1>

    <p>We're sorry, but your payment could not be processed at this time. There could be various reasons for this, such as:</p>
    <ul>
        <li>Incorrect payment details.</li>
        <li>Insufficient funds.</li>
        <li>Network issues during the transaction.</li>
    </ul>
    <p>Please try again, or if you continue to experience issues, feel free to contact our customer support team.</p>

    <!-- Modify the "Try Again" button link to include order_id -->
    <a href="card.php?order_id=<?php echo htmlspecialchars($orderId); ?>" class="button">Try Again</a>
    <a href="../index.php" class="back-button">Go Back to Home</a>
</div>

</body>
</html>

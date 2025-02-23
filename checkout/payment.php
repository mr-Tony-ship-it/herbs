<?php
include '../includes/db.php'; // Include the database connection
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$cartItems = [];

// Get cart items for the user
$stmt = $conn->prepare("SELECT cart.product_id, products.name, products.price, cart.quantity, products.stock_quantity
                        FROM cart 
                        JOIN products ON cart.product_id = products.id 
                        WHERE cart.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartItemsResult = $stmt->get_result();

$grandTotal = 0;
while ($item = $cartItemsResult->fetch_assoc()) {
    $cartItems[] = $item;
    $grandTotal += $item['price'] * $item['quantity'];
}

// Handle form submission for payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and fetch form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $zip = mysqli_real_escape_string($conn, $_POST['zip']);
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['payment_method']);

    // Ensure all data is present
    if (empty($name) ||empty($phone)|| empty($address) || empty($city) || empty($zip) || empty($paymentMethod)) {
        echo "Please fill in all the fields.";
        exit();
    }

    // Check if we have an existing order ID from a failed attempt
    if (isset($_GET['order_id'])) {
        // If order_id is provided, update the existing order
        $orderId = $_GET['order_id'];
        $paymentStatus = "pending";  // Reset the payment status for retry

        // Update the order in the orders table (revert the order status to pending)
        $stmt = $conn->prepare("UPDATE orders SET total = ?, phone= ?, address = ?, city = ?, zip = ?, payment_method = ?, status = ?, order_date = NOW() WHERE id = ? AND user_id = ?");
        if ($stmt === false) {
            die('Error preparing order update: ' . $conn->error);
        }

        $stmt->bind_param("dsssssssi", $grandTotal, $address, $phone, $city, $zip, $paymentMethod, $paymentStatus, $orderId, $userId);
        if (!$stmt->execute()) {
            die('Error executing order update: ' . $stmt->error);
        }
    } else {
        // Simulate payment status (for demonstration)
        $paymentStatus = "pending"; // For real payment, you'd interact with a payment gateway
        $orderDate = date("Y-m-d H:i:s");

        // Insert a new order into the orders table
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, phone, address, city, zip, payment_method, status, order_date) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die('Error preparing order insert: ' . $conn->error);
        }

        $stmt->bind_param("idsssssss", $userId, $grandTotal, $phone, $address, $city, $zip, $paymentMethod, $paymentStatus, $orderDate);

        if ($stmt->execute()) {
            $orderId = $stmt->insert_id;  // Get the inserted order ID
        } else {
            die('Error executing order insert: ' . $stmt->error);
        }
    }

    // Insert each cart item into the order_items table and update the stock
    foreach ($cartItems as $item) {
        $productId = $item['product_id'];

        // Insert into order_items table
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            die('Error preparing order_items insert: ' . $conn->error);
        }

        $stmt->bind_param("iiid", $orderId, $productId, $item['quantity'], $item['price']);
        if (!$stmt->execute()) {
            die('Error executing order_items insert: ' . $stmt->error);
        }

        // Decrease product stock in products table
        $newQuantity = $item['stock_quantity'] - $item['quantity'];
        if ($newQuantity < 0) {
            die('Error: Insufficient stock for product ID ' . $productId);
        }

        $stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
        if ($stmt === false) {
            die('Error preparing stock update: ' . $conn->error);
        }

        $stmt->bind_param("ii", $newQuantity, $productId);
        if (!$stmt->execute()) {
            die('Error executing stock update: ' . $stmt->error);
        }
    }

    // Redirect to the success page after everything is completed
    header("Location: card.php?order_id=" . $orderId);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="../css/nav.css">
    <style>
        /* Add some basic styles for the payment page */
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
            display: flex;
            justify-content: space-between;
        }

        .section-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: bold;
            display: block;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .button {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            font-size: 18px;
            width: 100%;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #45a049;
        }

        .cart-summary {
            width: 40%;
            border-left: 1px solid #ddd;
            padding-left: 20px;
        }

        .cart-summary h3 {
            font-size: 20px;
            margin-bottom: 15px;
        }

        .cart-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-summary table th, .cart-summary table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

    </style>
</head>
<body>

<!-- Navbar -->
<div class="navba">
    <h3 class="logo"></h3>
    <div class="nav-links">
        <a href="../index.php" class="nav-link">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="../user/cart.php" class="nav-link">Cart</a>
            <a href="../user/account_settings.php" class="nav-link">Profile</a>
            <a href="../logout.php" class="nav-link">Logout</a>
        <?php else: ?>
            <a href="../user/cart.php" class="nav-link">Cart</a>
            <a href="../user/login.php" class="nav-link">Login</a>
            <a href="../user/register.php" class="nav-link">Sign Up</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <div class="payment-details">
        <h1 class="section-title">Payment Details</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="phone">Contact Number</label>
                <input type="text" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="address">Shipping Address</label>
                <textarea id="address" name="address" required rows="4" cols="50"></textarea>
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" required>
            </div>
            <div class="form-group">
                <label for="zip">Postal Code</label>
                <input type="text" id="zip" name="zip" required>
            </div>
            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="credit_card">Credit Card</option>
                    <option value="paypal">PayPal</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>
            <button type="submit" class="button">Complete Payment</button>
        </form>
    </div>

    <div class="cart-summary">
        <h3>Cart Summary</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h3>Total: Rs. <?php echo number_format($grandTotal, 2); ?></h3>
    </div>
</div>

</body>
</html>

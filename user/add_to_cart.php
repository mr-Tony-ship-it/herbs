<?php
session_start();
include '../includes/db.php';  // Include the database connection

// Get raw POST data and decode it
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true); // Convert JSON string into an associative array

// Extract product_id and quantity from the data
$productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

// Log the received values for debugging
error_log('Received Product ID: ' . $productId);
error_log('Received Quantity: ' . $quantity);

// Validate product_id and quantity
if ($productId == 0 || $quantity == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity.']);
    exit;
}

// Check if the product exists in the database
$stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

if ($isLoggedIn) {
    // If the user is logged in, store the cart item in the database
    // Check if the product already exists in the user's cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // If the product already exists in the cart, update the quantity
        $stmt->bind_result($cartId, $existingQuantity);
        $stmt->fetch();
        $newQuantity = $existingQuantity + $quantity;

        // Update the cart item quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $newQuantity, $cartId);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cart updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating cart.']);
        }
    } else {
        // If the product doesn't exist in the cart, insert a new cart item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userId, $productId, $quantity);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product added to cart.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding product to cart.']);
        }
    }
} else {
    // For guests (non-logged-in users), store the cart in a cookie
    // Get the current cart (either empty or existing)
    $cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

    // If the product already exists in the cart, update the quantity
    if (isset($cart[$productId])) {
        $cart[$productId] += $quantity;
    } else {
        // Add a new product to the cart
        $cart[$productId] = $quantity;
    }

    // Store the updated cart in the cookie
    setcookie('cart', json_encode($cart), time() + (86400 * 30), "/");  // Cookie expires in 30 days

    echo json_encode(['success' => true, 'message' => 'Product added to cart (guest).']);
}
?>

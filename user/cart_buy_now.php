<?php
session_start();
include '../includes/db.php'; // Database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first.']);
    exit;
}

// Get the product ID and quantity
$data = json_decode(file_get_contents("php://input"), true);
$product_id = intval($data['product_id']);
$quantity = intval($data['quantity']);
$user_id = $_SESSION['user_id'];

// Check if the product exists and has sufficient stock
$product_query = $conn->prepare("SELECT * FROM products WHERE id = ?");
$product_query->bind_param("i", $product_id);
$product_query->execute();
$product = $product_query->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

if ($product['stock_quantity'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock.']);
    exit;
}

// Clear the existing cart
$clear_cart_query = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$clear_cart_query->bind_param("i", $user_id);
$clear_cart_query->execute();

// Add the new item to the cart
$add_to_cart_query = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
$add_to_cart_query->bind_param("iii", $user_id, $product_id, $quantity);
$add_to_cart_query->execute();

// Check if the insert was successful
if ($add_to_cart_query->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Product added to cart and ready for checkout.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add product to cart.']);
}
?>

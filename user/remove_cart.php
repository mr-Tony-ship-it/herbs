<?php
session_start();
include '../includes/db.php';  // Include database connection

// Get the product ID from the request
$data = json_decode(file_get_contents('php://input'), true);
$productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;

if ($productId == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
    exit;
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

if ($isLoggedIn) {
    // Remove the product from the user's cart in the database
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product removed from cart.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error removing product from cart.']);
    }
} else {
    // If the user is not logged in, remove from cookies
    if (isset($_COOKIE['cart'])) {
        $cart = json_decode($_COOKIE['cart'], true);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            setcookie('cart', json_encode($cart), time() + (86400 * 30), "/");
            echo json_encode(['success' => true, 'message' => 'Product removed from cart.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found in cart.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No cart found.']);
    }
}
?>

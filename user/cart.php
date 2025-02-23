<?php
session_start();
include '../includes/db.php';  // Include database connection

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Initialize cart
$cart = [];

// If the user is logged in, fetch cart items from the database
if ($isLoggedIn) {
    // Query to fetch cart items with stock information
    $stmt = $conn->prepare("SELECT c.product_id, p.name, p.price, c.quantity, p.image, p.stock_quantity 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Process each product in the cart
    while ($row = $result->fetch_assoc()) {
        // Only add items to the cart if they are in stock
        if ($row['stock_quantity'] > 0) {
            $cart[] = [
                'product_id' => $row['product_id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'quantity' => $row['quantity'],
                'image' => $row['image'],
                'stock_quantity' => $row['stock_quantity']
            ];
        } else {
            // If the product is out of stock, remove it from the cart
            $removeStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $removeStmt->bind_param("ii", $userId, $row['product_id']);
            $removeStmt->execute();
        }
    }
} else {
    // If the user is not logged in, fetch cart items from cookies
    $cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

    // If cookie data is only product IDs, we need to fetch product details for each product ID
    if ($cart && is_array($cart) && is_numeric($cart[0])) {
        // Fetch product details for each product ID stored in the cookie
        $placeholders = implode(',', array_fill(0, count($cart), '?'));
        $stmt = $conn->prepare("SELECT id, name, price, image, stock_quantity FROM products WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($cart)), ...$cart); // Bind all product IDs
        $stmt->execute();
        $result = $stmt->get_result();
        
        $cart = []; // Rebuild the cart with detailed product information
        while ($row = $result->fetch_assoc()) {
            // Only add items to the cart if they are in stock
            if ($row['stock_quantity'] > 0) {
                $cart[] = [
                    'product_id' => $row['id'],
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'quantity' => 1,  // Default to 1 if no quantity is saved
                    'image' => $row['image'],
                    'stock_quantity' => $row['stock_quantity']
                ];
            }
        }
    }
}

// Calculate the total price
$total = 0;
foreach ($cart as $item) {
    // Ensure $item is an array and contains valid keys before accessing them
    if (is_array($item) && isset($item['price'], $item['quantity'])) {
        $total += $item['price'] * $item['quantity'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/cart.css">
</head>
<body>

<!-- Navbar -->
<div class="navba">
    <h3 class="logo"></h3>
    <div class="nav-links">
        <a href="../index.php" class="nav-link">Home</a>
        <?php if ($isLoggedIn): ?>
            <a href="account_settings.php" class="nav-link">Account</a>
            <a href="../logout.php" class="nav-link">Logout</a>
        <?php else: ?>
            <a href="login.php" class="nav-link">Login</a>
            <a href="register.php" class="nav-link">Join</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <?php if (count($cart) > 0): ?>

    <!-- Cart Table -->
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart as $item): ?>
                <tr>
                    <td class="cart-item">
                        <img src="../uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    </td>
                    <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                    <td class="quantity">
                        <?php if ($item['stock_quantity'] == 0): ?>
                            <p>Out of Stock</p>
                        <?php elseif ($item['stock_quantity'] == 1): ?>
                            <p>Only 1 left</p>
                        <?php else: ?>
                            <input type="number" value="<?php echo min($item['quantity'], $item['stock_quantity']); ?>" 
                                   min="1" max="<?php echo $item['stock_quantity']; ?>" 
                                   data-product-id="<?php echo $item['product_id']; ?>">
                        <?php endif; ?>
                    </td>
                    <td>Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    <td class="actions">
                        <button onclick="removeItem(<?php echo $item['product_id']; ?>)">Remove</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        <strong>Total:</strong> Rs. <?php echo number_format($total, 2); ?>
    </div>

    <div class="cart-actions">
        <button onclick="checkout()">Proceed to Checkout</button>
    </div>

    <?php else: ?>
    <div class="empty-cart">
        <p>Your cart is empty. Start shopping!</p>
        <a href="../index.php" class="btn">Go to Shop</a>
    </div>
    <?php endif; ?>
</div>

<script>
    // Update quantity in cart
    document.querySelectorAll('.quantity input').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.getAttribute('data-product-id');
            const quantity = this.value;

            // Check if the quantity exceeds available stock
            const stockQuantity = parseInt(this.getAttribute('max'));
            if (quantity > stockQuantity) {
                alert("Cannot exceed available stock. Only " + stockQuantity + " left.");
                this.value = stockQuantity;
                return;
            }

            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: productId, quantity: quantity })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cart updated successfully');
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        });
    });

    // Remove item from cart
    function removeItem(productId) {
        if (confirm("Are you sure you want to remove this item?")) {
            fetch('remove_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Item removed from cart');
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    }

    // Proceed to checkout
    function checkout() {
        window.location.href = '../checkout/payment.php'; // Redirect to checkout page
    }
</script>

</body>
</html>

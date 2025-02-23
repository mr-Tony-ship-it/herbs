<?php
include 'includes/db.php'; // Assuming you have a database connection here

session_start(); // Start the session

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$cartCount = 0; // Default value if not logged in

// If the user is logged in, fetch the number of items in the cart
if ($isLoggedIn) {
    $userId = $_SESSION['user_id']; // Get the user ID from the session
    $cartQuery = "SELECT COUNT(*) AS cart_items FROM cart WHERE user_id = ?";
    
    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare($cartQuery);
    $stmt->bind_param('i', $userId); // 'i' stands for integer
    $stmt->execute();
    $stmt->bind_result($cartCount);
    $stmt->fetch();
    $stmt->close();
}

// Fetch categories for the navbar
$categoriesQuery = "SELECT * FROM categories";
$categoriesResult = $conn->query($categoriesQuery);

// Fetch the selected category
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Check if category ID is valid
if ($category_id <= 0) {
    die("Invalid category ID.");
}

// Fetch category name
$categoryQuery = "SELECT category_name FROM categories WHERE category_id = ?";
$stmt = $conn->prepare($categoryQuery);
$stmt->bind_param('i', $category_id);
$stmt->execute();
$categoryResult = $stmt->get_result();
$category = $categoryResult->fetch_assoc();

if (!$category) {
    die("Category not found.");
}
$stmt->close();

// Fetch products for the selected category
$productQuery = "SELECT * FROM products WHERE category_id = ?";
$stmt = $conn->prepare($productQuery);
$stmt->bind_param('i', $category_id);
$stmt->execute();
$productResult = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['category_name']); ?> Products</title>
    <link rel="stylesheet" href="css/index.css">
    <style>
/* Navbar Styles */
.navba {
    background-color: #2c3e50; /* Green background */
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.navba .logo {
    font-size: 24px;
    color: white;
    text-decoration: none;
}

.nav-links {
    display: flex;
    gap: 20px;
}

.nav-link {
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    background-color: #333;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.nav-link:hover {
    background-color: #ddd;
    color: #333;
}

/* Modal Button Styling */
button, .button {
    font-size: 16px;
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover, .button:hover {
    background-color: #45a049;
}

button:disabled, .button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.modal-content {
    background-color: #fff;
    padding: 30px;
    border-radius: 10px;
    width: 80%;
    max-width: 500px;
}

.modal-header {
    font-size: 24px;
    margin-bottom: 15px;
    color: #4CAF50;
}

.modal-body {
    margin-bottom: 20px;
}

.modal-footer {
    text-align: right;
}

.modal-button {
    padding: 10px 15px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.modal-button:hover {
    background-color: #45a049;
}

/* Close Button */
.close-btn {
    padding: 10px;
    background-color: red;
    color: white;
    border: none;
    border-radius: 50%;
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    .navba {
        flex-direction: column;
        align-items: flex-start;
    }

    .product-container {
        grid-template-columns: 1fr 1fr;
    }

    .category {
        padding: 10px;
    }
}

</style>

</head>

<body>

    <!-- Navbar -->
    <div class="navba">
    <h3 class="logo"><?php echo htmlspecialchars($category['category_name']); ?></h3>
    <div class="nav-links">
        <a href="index.php" class="nav-link">Home</a>
        <a href="about.php" class="nav-link">About</a>
        <?php if ($isLoggedIn): ?>
            <a href="user/cart.php" class="nav-link">Cart (<?php echo $cartCount; ?>)</a>
            <a href="user/account_settings.php" class="nav-link">Account</a>
            <a href="logout.php" class="nav-link">Logout</a>
        <?php else: ?>
            <a href="user/login.php" class="nav-link">Login</a>
            <a href="user/register.php" class="nav-link">Join</a>
        <?php endif; ?>
    </div>
</div>

    <!-- Categories -->
    <div class="category">
        <h2>Categories</h2>
        <ul class="category-list">
            <?php while ($categoryItem = $categoriesResult->fetch_assoc()): ?>
                <li class="category-item">
                    <a href="products_category.php?category_id=<?php echo $categoryItem['category_id']; ?>">
                        <?php echo htmlspecialchars($categoryItem['category_name']); ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <!-- Products -->
    <div class="product-container">
        <?php if ($productResult->num_rows > 0): ?>
            <?php while ($product = $productResult->fetch_assoc()): ?>
                <div class="product-block" data-product-id="<?= $product['id'] ?>">
                    <a href="view_product.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                        <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                        <h4><?php echo htmlspecialchars($product['name']); ?> - Rs. <?php echo number_format($product['price'], 2); ?></h4>
                        <?php
                        $stock_quantity = $product['stock_quantity'];
                        if ($stock_quantity == 0) {
                            $stock_status = "Out of stock";
                            $status_class = "out-of-stock";
                        } elseif ($stock_quantity > 0 && $stock_quantity <= 2) {
                            $stock_status = "Only $stock_quantity left";
                            $status_class = "critical-stock";
                        } else {
                            $stock_status = "";
                            $status_class = "";
                        }
                        ?>
                        <p class="<?= $status_class; ?>"><?= $stock_status; ?></p>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    <script type="text/javascript" src="js/add_to_cart.js"></script>
    <script>
        function showMessage(message, type='') {
            const messageBox = document.getElementById('message-box');
            messageBox.className = 'message-box ' + type;
            messageBox.innerHTML = message;
            messageBox.style.display = 'block';
            setTimeout(() => {
                messageBox.style.display = 'none';
            }, 3000);
        }
    </script>

</body>
</html>

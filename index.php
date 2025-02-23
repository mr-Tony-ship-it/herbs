<?php
include 'includes/db.php'; // Database connection
session_start(); // Start the session

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Fetch categories for the navbar
$categoriesResult = $conn->query("SELECT * FROM categories");

// Fetch products for display
$result = $conn->query("SELECT * FROM products");

$searchTerm = '';
if (isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    $query = "SELECT * FROM products WHERE name LIKE '%$searchTerm%'";
} else {
    $query = "SELECT * FROM products"; // Default query
}
$result = $conn->query($query);


// Count items in the cart for the logged-in user

$cartCount = 0;
if ($isLoggedIn) {
    $cartCountResult = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = " . $_SESSION['user_id']);
    $cartCountData = $cartCountResult->fetch_assoc();
    $cartCount = $cartCountData['total'] ?? 0; // Default to 0 if null
}
if (isset($_GET['success'])) {
    echo "<p style='color: green;'>Success: " . htmlspecialchars($_GET['success']) . "</p>";
} elseif (isset($_GET['error'])) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($_GET['error']) . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery -->
    <link rel ="stylesheet" href="css/index.css"/>
    
</head>
<body>

<div class="navbar">
    <h1>Herb Haven</h1>
    <div>
    <div class="search-bar">
        <form action="" method="get">
            <input type="text" id="search-input" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search products..." />
            <button type="submit">Search</button>
        </form>
        <a href="about.php" class="button">About</a>
        <?php if ($isLoggedIn): ?>
            <a href="user/cart.php" class="button">Cart (<?php echo $cartCount; ?>)</a>
            <a href="user/account_settings.php" class="button">Account</a>
            <a href="logout.php" class="button">Logout</a>
        <?php else: ?>
            <a href="user/register.php" class="button logout-button">Join</a>
            <a href="user/login.php" class="button logout-button">Login</a>
        <?php endif; ?>
    </div>
    </div>
</div>

<div class="support-button">
  <a href="mailto:herbs@herbs.studio.com" class="support-btn">Support</a>
</div>

<div class="category">
    <h2>Categories</h2>
    <ul class="category-list">
        <?php while ($category = $categoriesResult->fetch_assoc()): ?>
            <li class="category-item">
                <a href="products_category.php?category_id=<?php echo $category['category_id']; ?>">
                    <?php echo htmlspecialchars($category['category_name']); ?>
                </a>
            </li>
        <?php endwhile; ?>
    </ul>
</div>

<div class="container">
    <h3>Products</h3>
    <div class="product-container">
        <?php while ($product = $result->fetch_assoc()): ?>
            <div class="product-block" data-product-id="<?= $product['id'] ?>">
                <a href="view_product.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                    <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="product-image">
                    <h4><?= htmlspecialchars($product['name']); ?> - Rs. <?= number_format($product['price'], 2); ?></h4>
                    
                    <?php
                    $stock_quantity = $product['stock_quantity'];
                    if ($stock_quantity == 0) {
                        $stock_status = "Out of stock";
                        $status_class = "out-of-stock";
                    } elseif ($stock_quantity > 0 &&$stock_quantity <= 2) {
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
</div>
<script>
 window.onload = function() {
            // Check if the search query exists in the URL
            if (window.location.search.includes('search')) {
                // Clear the search input field (Optional, if you want to reset the input after refresh)
                document.getElementById('search-input').value = '';

                // Remove the search term from the URL without refreshing the page
                window.history.pushState({}, '', window.location.pathname); // This removes the search query from the URL
            }
        }

</script>
<?php include 'includes/footer.php'; ?>

</body>
</html>

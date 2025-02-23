<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
     header("Location: ../user/login.php");
    exit; }

include '../includes/db.php'; // Assuming you have a database connection here

// Fetch categories for the navbar
$categoriesResult = $conn->query("SELECT * FROM categories");

// Fetch products for display (with search support)
$searchTerm = '';
if (isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    $query = "SELECT * FROM products WHERE name LIKE '%$searchTerm%'";
} else {
    $query = "SELECT * FROM products"; // Default query
}
$result = $conn->query($query);

// Check stock levels to generate alerts
$outOfStockProducts = [];
$criticalStockProducts = [];

// Query to check stock levels for each product
$stockResult = $conn->query("SELECT id, name, stock_quantity FROM products");

while ($product = $stockResult->fetch_assoc()) {
    if ($product['stock_quantity'] == 0) {
        // Product is out of stock
        $outOfStockProducts[] = $product['name'];
    } elseif ($product['stock_quantity'] <= 2) {
        // Product is in critical stock (<= 2 units)
        $criticalStockProducts[] = $product['name'];
    }
}

// Prepare the stock alert message
$alertMessage = '';
if (count($outOfStockProducts) > 0 || count($criticalStockProducts) > 0) {
    $alertMessage = "<strong>⚠️ Low Stock Alerts:</strong><br>";

    if (count($outOfStockProducts) > 0) {
        $alertMessage .= "<strong>Out of Stock:</strong> " . implode(", ", $outOfStockProducts) . "<br>";
    }
    if (count($criticalStockProducts) > 0) {
        $alertMessage .= "<strong>Critical Stage:</strong> " . implode(", ", $criticalStockProducts) . "<br>";
    }
}

if (isset($_GET['success'])) {
    echo '<p class="success">' . htmlspecialchars($_GET['success']) . '</p>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/adminIn.css">
</head>
<body>

    <div class="navbar">
        <h1>Admin Dashboard</h1>
        <div>
        <div class="search-bar">
        <form action="" method="get">
            <input type="text" id="search-input" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search products..." />
            <button type="submit">Search</button>
        </form>
            <a href="view_feedback.php" class="button">Feedbacks</a>
            <a href="view_users.php" class="button">View Users</a>
            <a href="add_item.php" class="button">Add New Product</a>
            <a href="../logout.php" class="button logout-button">Logout</a>
        </div>
    </div>
</div>

    <!-- Display Low Stock Alert -->
    <?php if ($alertMessage): ?>
        <div class="alert" id="stock-alert">
            <span class="close-btn" onclick="closeAlert()">×</span>
            <p><?php echo $alertMessage; ?></p>
        </div>
    <?php endif; ?>
   

    <div class="category">
        <h2>Categories</h2>
        <ul class="category-list">
            <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                <li class="category-item">
                    <a href="products_by_category.php?category_id=<?php echo $category['category_id']; ?>">
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
                <div class="product-block">
                    <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                    <h4><?php echo htmlspecialchars($product['name']); ?> - Rs.<?php echo number_format($product['price'], 2); ?></h4>
                    <a href="update_item.php?id=<?php echo $product['id']; ?>" class="button">Update</a>
                    <a href="delete_item.php?id=<?php echo $product['id']; ?>" class="button">Delete</a>
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

</body>
</html>

<?php
include 'includes/db.php';
include 'includes/header.php';

// Fetch categories from the database
$categoryResult = $conn->query("SELECT * FROM categories");

if (!$categoryResult) {
    // Log the error or display a message
    die("Database query failed: " . $conn->error);
}

// Fetch products from the database
$result = $conn->query("SELECT * FROM products LIMIT 10"); // Fetching 10 products for display
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herbal Haven - Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Your existing CSS styles */
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Categories</h2>
        <ul>
            <?php if ($categoryResult->num_rows > 0): ?>
                <?php while ($category = $categoryResult->fetch_assoc()): ?>
                    <li><a href="category.php?id=<?= $category['category_id'] ?>"><?= htmlspecialchars($category['category_name']) ?></a></li>
                <?php endwhile; ?>
            <?php else: ?>
                <li>No categories found.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="main-content">
        <h3>Available Products</h3>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($product = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p>$<?= number_format($product['price'], 2) ?></p>
                    <button class="submit-btn">Add to Cart</button>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

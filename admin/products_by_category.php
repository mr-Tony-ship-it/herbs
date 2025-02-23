<?php
include '../includes/db.php'; // Assuming you have a database connection here

// Fetch the selected category
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Check if category ID is valid
if ($category_id <= 0) {
    die("Invalid category ID.");
}

// Fetch category name
$categoryResult = $conn->query("SELECT category_name FROM categories WHERE category_id = $category_id");

if (!$categoryResult) {
    die("Database query failed: " . $conn->error);
}

$category = $categoryResult->fetch_assoc();

// Check if category exists
if (!$category) {
    die("Category not found.");
}

// Fetch products for the selected category
$productResult = $conn->query("SELECT * FROM products WHERE category_id = $category_id");

if (!$productResult) {
    die("Database query failed: " . $conn->error);
}

// Fetch categories for the navbar
$categoriesResult = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['category_name']); ?> Products</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css"/>
    <link rel="stylesheet" href="../css/adminIn.css">

    <style>
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 20px;
        }
        h2 {
            color: #2c3e50;
        }
        .product-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px; /* Adjust spacing between blocks as needed */
        }
        .product-block {
            flex: 1 1 calc(33.333% - 20px); /* Three blocks per row */
            box-sizing: border-box;
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
            background: white; /* Optional: Add background color to blocks */
            border-radius: 10px; /* Optional: Rounded corners */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Optional: Subtle shadow */
        }
        .button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .product-block img {
            max-width: 100%;
            height: auto; /* Maintain aspect ratio */
            border-radius: 10px; /* Optional: Rounded corners for images */
            margin-bottom: 10px; /* Space below the image */
        }
        .logout-button {
            background: #f44336;
        }
    </style>
</head>
<body>

<div class="navba">
    <h3 class="logo"></h3>
    <div class="nav-links">
            <a href="index.php" class="nav-link">Dashboard</a>
            <a href="add_item.php" class="nav-link">Add New Product</a>          
            <a href="../logout.php" class="nav-link">Logout</a>
        </div>
    </div>

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
        <h2><?php echo htmlspecialchars($category['category_name']); ?> Products</h2>
        <div class="product-container">
            <?php if ($productResult->num_rows > 0): ?>
                <?php while ($product = $productResult->fetch_assoc()): ?>
                    <div class="product-block">
                        <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                        <h4><?php echo htmlspecialchars($product['name']); ?> - Rs.<?php echo number_format($product['price'], 2); ?></h4>
                        <a href="update_item.php?id=<?php echo $product['id']; ?>" class="button">Update</a>
                        <a href="delete_item.php?id=<?php echo $product['id']; ?>" class="button">Delete</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>      
                <p>No products found in this category.</p>
            <?php endif; ?>
        </div>
        
        <a href="index.php" class="button">Back to Dashboard</a>
    </div>


</body>
</html>

<?php
include '../includes/db.php'; // Include your database connection

// Get the product ID from the URL
$product_id = intval($_GET['id']);

// Fetch the existing product details
$productResult = $conn->query("SELECT * FROM products WHERE id = $product_id");
if ($productResult->num_rows === 0) {
    die("Product not found.");
}

$product = $productResult->fetch_assoc();

// Fetch categories for the dropdown
$categoryResult = $conn->query("SELECT * FROM categories");

// Initialize variables with existing product values
$name = $product['name'];
$price = $product['price'];
$description = $product['description'];
$category_id = $product['category_id'];
$stock_quantity = $product['stock_quantity'];
$error = "";
$success = "";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $stock_quantity = trim($_POST['stock_quantity']);

    // Initialize image upload variables
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_size = $_FILES['image']['size'];
    $image_error = $_FILES['image']['error'];

    // Validate inputs
    if (empty($name) || empty($price) || empty($description) || $category_id <= 0) {
        $error = "Please fill in all fields correctly.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Please enter a valid price.";
    } elseif ($image_error !== UPLOAD_ERR_OK && $image_error !== UPLOAD_ERR_NO_FILE) {
        $error = "Error uploading image. Please try again.";
    } elseif ($image_size > 2000000) {
        $error = "Image size must be less than 2MB.";
    } else {
        // Prepare the SQL statement
        if (empty($image)) {
            // If no new image is uploaded, update without changing the image
            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, category_id = ?, stock_quantity = ? WHERE id = ?");
            $stmt->bind_param("sdssii", $name, $price, $description, $category_id, $stock_quantity, $product_id);
        } else {
            // Process image upload
            $target_directory = "../uploads/";
            if (!is_dir($target_directory)) {
                mkdir($target_directory, 0755, true);
            }

            $imageFileType = strtolower(pathinfo($image, PATHINFO_EXTENSION));
            $new_image_name = uniqid('', true) . '.' . $imageFileType;
            $target_file = $target_directory . $new_image_name;

            // Validate image file type
            $allowed_types = ['jpg', 'jpeg', 'png'];
            if (!in_array($imageFileType, $allowed_types)) {
                $error = "Only JPG, JPEG, PNG files are allowed.";
            } else {
                if (move_uploaded_file($image_tmp, $target_file)) {
                    // Update the image in the database along with other details
                    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ?, category_id = ?, stock_quantity = ? WHERE id = ?");
                    $stmt->bind_param("sdssiii", $name, $price, $description, $new_image_name, $category_id, $stock_quantity, $product_id);
                } else {
                    $error = "Error moving the uploaded file. Please check the permissions on the target directory.";
                }
            }
        }

        if (empty($error) && $stmt->execute()) {
            $success = "Product updated successfully!";
            header("Location: index.php"); // Redirect after successful update
            exit;
        } elseif ($stmt->error) {
            $error = "Error updating product: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="../css/adminIn.css" rel="stylesheet">
</head>
<body>
    <div class="navbar">
        <div>
            <a href="index.php" class="button">Dashboard</a>
            <a href="add_item.php" class="button">Add New Product</a>
            <a href="view_users.php" class="button">View Users</a>
            <a href="../logout.php" class="button">Logout</a>
        </div>
    </div>
    <div class="form-container">
        <h2>Update Product</h2>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Price (Rs.)</label>
                <input type="number" id="price" name="price" value="<?= htmlspecialchars($price) ?>" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required><?= htmlspecialchars($description) ?></textarea>
            </div>
            <div class="form-group">
                <label for="stock_quantity">Quantity</label>
                <input type="number" id="stock_quantity" name="stock_quantity" value="<?= htmlspecialchars($stock_quantity) ?>" required min="1">
            </div>
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <?php while ($category = $categoryResult->fetch_assoc()): ?>
                        <option value="<?= $category['category_id'] ?>" <?= $category_id == $category['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['category_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="image">Product Image (Leave blank to keep existing)</label>
                <input type="file" id="image" name="image" accept="image/*">
                <p>Current Image: <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="Current Image" style="max-width: 100px; height: auto;"></p>
            </div>
            <button type="submit" class="submit-btn">Update Product</button>
        </form>
    </div>
</body>
</html>

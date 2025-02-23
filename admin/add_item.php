<?php
include '../includes/db.php'; // Include your database connection

// Fetch categories for the dropdown
$categoryResult = $conn->query("SELECT * FROM categories");

// Initialize variables for form data
$name = $price = $description = $category_id = $stock_quantity = "";
$error = "";
$success = "";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $stock_quantity = intval($_POST['stock_quantity']); // Handle stock_quantity input

    // Initialize image upload variables
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_size = $_FILES['image']['size'];
    $image_error = $_FILES['image']['error'];

    // Validate inputs
    if (empty($name) || empty($price) || empty($description) || $category_id <= 0 || $stock_quantity <= 0) {
        $error = "Please fill in all fields correctly.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Please enter a valid price.";
    } elseif (!is_numeric($stock_quantity) || $stock_quantity <= 0) {
        $error = "Please enter a valid stock_quantity.";
    } elseif ($image_error !== UPLOAD_ERR_OK) {
        $error = "Error uploading image. Please try again.";
    } elseif ($image_size > 2000000) {
        $error = "Image size must be less than 2MB.";
    } else {
        // Process image upload
        $target_directory = "../uploads/";

        // Ensure the target directory exists
        if (!is_dir($target_directory)) {
            mkdir($target_directory, 0755, true);
        }

        $imageFileType = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $new_image_name = uniqid('', true) . '.' . $imageFileType;
        $target_file = $target_directory . $new_image_name;

        // Validate image file type
        $allowed_types = ['jpg', 'jpeg', 'png','webp'];
        if (!in_array($imageFileType, $allowed_types)) {
            $error = "Only JPG, JPEG, PNG files are allowed.";
        } else {
            // Move the uploaded file
            if (move_uploaded_file($image_tmp, $target_file)) {
                // Prepare and bind parameters for the database
                $stmt = $conn->prepare("INSERT INTO products (name, price, description, image, category_id, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sdssii", $name, $price, $description, $new_image_name, $category_id, $stock_quantity);

                if ($stmt->execute()) {
                    $success = "Product added successfully! Image uploaded as: " . htmlspecialchars($new_image_name);
                    // Reset form fields after successful insertion
                    $name = $price = $description = $stock_quantity = "";
                    $category_id = 0;
                } else {
                    $error = "Error adding product: " . $stmt->error;
                }
            } else {
                $error = "Error moving the uploaded file. Please check the permissions on the target directory.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="../css/adminIn.css" rel="stylesheet">
    <link href="../css/nav.css" rel="stylesheet">

</head>
<body>

<div class="navba">
    <h3 class="logo"></h3>
    <div class="nav-links">
            <a href="index.php" class="nav-link">Dashboard</a>
            <a href="view_users.php" class="nav-link">View Users</a>
            <a href="../logout.php" class="nav-link">Logout</a>
        </div>
    </div>
    
    <div class="form-container">
        <h2>Add New Product</h2>

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
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php while ($category = $categoryResult->fetch_assoc()): ?>
                        <option value="<?= $category['category_id'] ?>">
                            <?= htmlspecialchars($category['category_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="stock_quantity">stock_quantity</label>
                <input type="number" id="stock_quantity" name="stock_quantity" value="<?= htmlspecialchars($stock_quantity) ?>" required min="1">
            </div>
            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>
            <button type="submit" class="submit-btn">Add Product</button>
        </form>
    </div>
</body>
</html>

<?php
include '../includes/db.php';

// Check if ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Fetch the product to confirm deletion
    $product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();

    // Check if product exists
    if (!$product) {
        header("Location: index.php?error=Product not found"); // Redirect if product doesn't exist
        exit();
    }

    // Check if the form is submitted to delete the product
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: index.php?success=Product deleted successfully");
            exit();
        } else {
            $error = "Error deleting product. Please try again.";
        }
    }
} else {
    header("Location: index.php?error=No product ID provided"); // Redirect if no ID is provided
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            padding: 20px;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        .button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .cancel-button {
            background: #f44336;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Confirm Deletion</h2>
        
        <p>Are you sure you want to delete the product "<strong><?= htmlspecialchars($product['name']) ?></strong>"?</p>

        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        
        <form method="POST" action="">
            <button type="submit" class="button">Yes, Delete</button>
            <a href="index.php" class="button cancel-button">Cancel</a>
        </form>
    </div>
</body>
</html>

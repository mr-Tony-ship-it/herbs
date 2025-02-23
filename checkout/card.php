<?php
session_start();

// Retrieve order ID from the URL
$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;

// If no order_id is found, show an error
if (!$orderId) {
    echo "Invalid order ID.";
    exit();
}

// Process the form if data is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $card_number = trim($_POST['card_number']);
    $exp_date = trim($_POST['exp_date']);
    $cvv = trim($_POST['cvv']);

    // Basic validation: Check if all fields are provided
    if (empty($card_number) || empty($exp_date) || empty($cvv)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: payment-fail.php?order_id=" . $orderId);
        exit;
    }

    // Simple validation for card number (must be 16 digits)
    if (strlen($card_number) !== 10) {  // Corrected length to 16 digits
        $_SESSION['error_message'] = "Card number should be 16 digits.";
        header("Location: payment-fail.php?order_id=" . $orderId);
        exit;
    }

    // Simple validation for expiry date (must be in MM/YY format)
    if (strlen($exp_date) !== 5 ) {
        $_SESSION['error_message'] = "Expiration date must be in MM/YY format.";
        header("Location: payment-fail.php?order_id=" . $orderId);
        exit;
    }

    // Simple validation for CVV (must be 3 digits)
    if (strlen($cvv) !== 3 || !ctype_digit($cvv)) {
        $_SESSION['error_message'] = "CVV must be 3 digits.";
        header("Location: payment-fail.php?order_id=" . $orderId);
        exit;
    }

    // Simulate a successful payment (you would typically call a payment gateway here)
    // For now, we'll assume payment is always successful.

    // Redirect to the payment success page with the order_id
    header("Location: payment-success.php?order_id=" . $orderId);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Card Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card-form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .card-form h3 {
            text-align: center;
        }
        .card-form input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .card-form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        .card-form input[type="submit"]:hover {
            background-color: #45a049;
        }
        .card-form input[type="button"] {
            background-color: #f44336;
            color: white;
            cursor: pointer;
        }
        .card-form input[type="button"]:hover {
            background-color: #d32f2f;
        }
        .message {
            text-align: center;
            margin-top: 20px;
            color: red;
        }
    </style>
</head>
<body>

<div class="card-form">
    <h3>Enter Card Details</h3>
    
    <!-- Form to collect card details -->
    <form method="POST" action="card.php?order_id=<?php echo $orderId; ?>">
        <label for="card_number">Card Number:</label>
        <input type="text" id="card_number" name="card_number" placeholder="10 digits" required><br>

        <label for="exp_date">Expiration Date (MM/YY):</label>
        <input type="text" id="exp_date" name="exp_date" placeholder="MM/YY" required><br>

        <label for="cvv">CVV:</label>
        <input type="text" id="cvv" name="cvv" placeholder="CVV" required><br>

        <!-- Submit button for form submission -->
        <input type="submit" value="Submit">
    </form>

    <!-- Cancel button redirecting to cancel.php -->
    <form action="cancel.php" method="get">
        <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
        <input type="button" value="Cancel" onclick="window.location.href='payment_cancelled.php?order_id=<?php echo $orderId; ?>';">
    </form>

    <!-- Display error messages if any -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
</div>

</body>
</html>

<?php
include '../includes/db.php'; // Database connection

// Fetch product-wise sales data, including the number of unique buyers and total amount
$query = "
    SELECT 
        p.name AS product_name,
        COUNT(DISTINCT o.user_id) AS num_buyers,  -- Count unique buyers
        SUM(oi.quantity * p.price) AS total_amount  -- Total amount for that product
    FROM products p
    JOIN order_items oi ON p.id = oi.product_id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'completed'  -- Only completed orders
    GROUP BY p.name
    HAVING num_buyers > 0
    ORDER BY total_amount DESC
";

$transactionResult = $conn->query($query);

if (!$transactionResult) {
    die("Database query failed: " . $conn->error);
}

// Initialize variable for total sales
$total_sales = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Sales Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 20px;
        }
        h2 {
            color: #2c3e50;
        }
        .navbar {
            background: #4CAF50;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            color: #333;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .total-summary {
            font-size: 18px;
            font-weight: bold;
            margin-top: 30px;
            padding: 10px;
            background-color: #e7f7e7;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .print-button {
            background-color: #2196F3;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }
        .print-button:hover {
            background-color: #1976D2;
        }
    </style>
    <script>
        // Function to trigger print
        function printPage() {
            window.print();
        }
    </script>
</head>
<body>

<div class="navbar">
    <h1>Product Sales Report</h1>
    <div>
        <a href="index.php">Dashboard</a>
        <a href="../logout.php" class="button">Logout</a>
    </div>
</div>

<h2>Completed Product Sales Report</h2>

<table>
    <tr>
        <th>Product Name</th>
        <th>Number of Buyers</th>
        <th>Total Amount</th>
    </tr>

    <?php if ($transactionResult->num_rows > 0): ?>
        <?php while ($transaction = $transactionResult->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($transaction['product_name']); ?></td>
                <td><?php echo htmlspecialchars($transaction['num_buyers']); ?></td>
                <td><?php echo number_format($transaction['total_amount'], 2); ?> INR</td>
            </tr>
            <?php
            // Accumulate total sales across all products
            $total_sales += $transaction['total_amount'];
            ?>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="3">No completed sales found.</td>
        </tr>
    <?php endif; ?>
</table>

<!-- Total Sales Summary -->
<div class="total-summary">
    <h3>Total Sales: <?php echo number_format($total_sales, 2); ?> INR</h3>
</div>

<!-- Print Button -->
<a href="javascript:void(0);" class="print-button" onclick="printPage()">Print Report</a>

<a href="view_users.php" class="button">Back to Users</a>

</body>
</html>

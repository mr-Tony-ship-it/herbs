<?php
session_start(); // Start the session

// Include the database connection file
include('includes/db.php');

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get feedback and rating from the form
    $feedback = trim($_POST['feedback']);
    $rating = trim($_POST['rating']);
    
    // Validate the rating (only allow numbers 1 to 5)
    if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        $error_message = "Invalid rating. Please provide a rating between 1 and 5.";
    } else {
        // Prepare the SQL statement using MySQLi to insert feedback into the database
        $stmt = $conn->prepare("INSERT INTO feedbacks (feedback, rating) VALUES (?, ?)");
        
        // Bind parameters to the prepared statement
        $stmt->bind_param("si", $feedback, $rating); // "si" means string and integer
        
        // Attempt to execute the statement
        if ($stmt->execute()) {
            // Store success message in session
            $_SESSION['success_message'] = "Thank you for your feedback!";
        } else {
            // Store error message in session
            $_SESSION['error_message'] = "Sorry, there was an issue saving your feedback. Please try again later.";
        }
        
        // Close the statement
        $stmt->close();
    }
    
    // Redirect back to the same page (or another page)
    header("Location: index.php");
    exit;
}

// Close the database connection at the end of the script
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anonymous Feedback Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        label {
            font-weight: bold;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            text-align: center;
            margin-top: 20px;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Anonymous Feedback Form</h2>
    
    <!-- Display success or error message -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="message success">
            <?php echo $_SESSION['success_message']; ?>
            <?php unset($_SESSION['success_message']); // Clear the message ?>
        </div>
    <?php elseif (isset($_SESSION['error_message'])): ?>
        <div class="message error">
            <?php echo $_SESSION['error_message']; ?>
            <?php unset($_SESSION['error_message']); // Clear the message ?>
        </div>
    <?php endif; ?>

    <!-- The form -->
    <form action="feedback_form.php" method="POST">
        <label for="feedback">Your Feedback:</label>
        <textarea id="feedback" name="feedback" rows="4" required></textarea>
        
        <label for="rating">Rate Us (1 to 5):</label>
        <input type="text" id="rating" name="rating" pattern="[1-5]" title="Please enter a number between 1 and 5" required>
        
        <input type="submit" value="Submit Feedback">
    </form>
    
    <div class="message">
        <p>Your feedback is anonymous and greatly appreciated!</p>
    </div>
</div>

</body>
</html>

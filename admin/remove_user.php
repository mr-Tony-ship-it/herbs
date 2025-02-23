<?php
include '../includes/db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the user ID from the form
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    // Check if the user ID is valid
    if ($userId > 0) {
        // Update the user's status to blocked
        $stmt = $conn->prepare("delete from users  WHERE id = ?");
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            // Redirect back with a success message
            header("Location: view_users.php?message=User removed successfully.");
            exit;
        } else {
            // Handle error
            echo "Error blocking user: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Invalid user ID.";
    }
}

$conn->close();
?>

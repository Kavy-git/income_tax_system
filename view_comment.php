<?php
session_start();
include 'db.php';

// Get the user_id from session
$user_id = trim($_SESSION['user_id']);  // Ensure there are no extra spaces
$comment = $_POST['comment'];  // Get the comment

// Debugging: Print the user_id to ensure it is set correctly
echo "Session UserID: " . $user_id . "<br>";  // Make sure this is correctly set

// Step 1: Get the TaxpayerID corresponding to the user_id
$check_user_query = "SELECT TaxpayerID FROM Taxpayer WHERE user_id = ?";
$check_user_stmt = $conn->prepare($check_user_query);
$check_user_stmt->bind_param("i", $user_id);  // Bind the user_id as an integer
$check_user_stmt->execute();
$check_user_stmt->store_result();

// Debugging: Print the query and number of rows
echo "Query: " . $check_user_query . " with UserID: " . $user_id . "<br>";
echo "Rows returned: " . $check_user_stmt->num_rows . "<br>";

// If no user found, show error
if ($check_user_stmt->num_rows == 0) {
    die('Error: UserID does not exist in the Taxpayer table.');
}

// Fetch the TaxpayerID
$check_user_stmt->bind_result($taxpayer_id);
$check_user_stmt->fetch();

// Debugging: Ensure TaxpayerID is correctly fetched
echo "TaxpayerID fetched: " . $taxpayer_id . "<br>";

// Step 2: Insert the comment into the notification table with the correct TaxpayerID
$notification_query = "INSERT INTO Notification (UserID, Message, Timestamp, Type, Status) VALUES (?, ?, NOW(), ?, ?)";
$notification_stmt = $conn->prepare($notification_query);

// Check if the statement was prepared correctly
if ($notification_stmt === false) {
    die('Error preparing the SQL query: ' . $conn->error);
}

// Define notification values
$message = "Comment added by professional: $comment";  // The message
$type = 'Info';  // Default type
$status = 'Unread';  // Default status

// Bind parameters for notification insertion
$notification_stmt->bind_param("isss", $taxpayer_id, $message, $type, $status);

// Execute the query
if ($notification_stmt->execute()) {
    echo "Comment added and notification sent successfully!";
} else {
    echo "Error inserting notification: " . $notification_stmt->error;
}

// Close the statement
$notification_stmt->close();
?>

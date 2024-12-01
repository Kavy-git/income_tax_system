<?php
session_start();
include 'db.php';

$client_id = $_GET['client_id'];
$view_option = isset($_GET['view_option']) ? $_GET['view_option'] : 'recent'; // Default to 'recent'

// Fetch client details
$client_query = "SELECT * FROM Taxpayer WHERE user_id = ?";
$client_stmt = $conn->prepare($client_query);

if (!$client_stmt) {
    die("Error preparing client query: " . $conn->error);
}

$client_stmt->bind_param("i", $client_id);
$client_stmt->execute();
$client = $client_stmt->get_result()->fetch_assoc();

if (!$client) {
    die("No client data found for this ID.");
}

// Fetch client revenue (updated query to remove 'date' field)
$revenue_query = "SELECT amount FROM tax_revenues WHERE user_id = ?";
$revenue_stmt = $conn->prepare($revenue_query);

if (!$revenue_stmt) {
    die("Error preparing revenue query: " . $conn->error);
}

$revenue_stmt->bind_param("i", $client_id);
$revenue_stmt->execute();
$revenues = $revenue_stmt->get_result();

// Fetch client files based on view option
if ($view_option == 'recent') {
    $file_query = "SELECT file_path, upload_date FROM documents WHERE user_id = ? ORDER BY upload_date DESC LIMIT 1";
} else {
    $file_query = "SELECT file_path, upload_date FROM documents WHERE user_id = ?";
}

$file_stmt = $conn->prepare($file_query);

if (!$file_stmt) {
    die("Error preparing file query: " . $conn->error);
}

$file_stmt->bind_param("i", $client_id);
$file_stmt->execute();
$files = $file_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Details</title>
    <style>
        /* General Dark Theme Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #fff;
            margin: 0;
            padding: 0;
        }

        h1, h2, h3 {
            color: #35F374; /* Green color for headings */
        }

        /* Container Styling */
        .container {
            margin: 20px;
        }

        /* Client Details */
        .client-details {
            background-color: #1f1f1f;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            margin-bottom: 20px;
        }

        .client-details h2 {
            margin-top: 0;
        }

        .client-details ul {
            list-style: none;
            padding: 0;
        }

        .client-details li {
            background-color: #2a2a2a;
            margin: 5px 0;
            padding: 10px;
            border-radius: 5px;
        }

        .client-details li a {
            color: #35F374;
            text-decoration: none;
        }

        .client-details li a:hover {
            color: #fff;
        }

        /* Comment Form */
        .comment-form {
            background-color: #1f1f1f;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            margin-top: 20px;
        }

        .comment-form textarea {
            width: 100%;
            padding: 10px;
            background-color: #2a2a2a;
            color: #fff;
            border: 1px solid #444;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .comment-form button {
            background-color: #35F374;
            color: #121212;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .comment-form button:hover {
            background-color: #2a9d5d;
        }

        /* Styling for the view options */
        .file-options {
            margin-bottom: 20px;
        }

        .file-options .btn {
            background-color: #35F374;
            color: #121212;
            padding: 10px 20px;
            margin-right: 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
        }

        .file-options .btn:hover {
            background-color: #2a9d5d;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Client: <?php echo htmlspecialchars($client['Name']); ?></h1>

    <div class="client-details">
        <h2>Revenues</h2>
        <ul>
            <?php while ($revenue = $revenues->fetch_assoc()) { ?>
                <li>Amount: <?php echo htmlspecialchars($revenue['amount']); ?></li>
            <?php } ?>
        </ul>
    </div>

    <div class="client-details">
        <h2>Files</h2>
        
        <!-- Add buttons for viewing recent or all files -->
        <div class="file-options">
            <a href="?client_id=<?php echo $client_id; ?>&view_option=recent" class="btn">View Recent File</a>
            <a href="?client_id=<?php echo $client_id; ?>&view_option=all" class="btn">View All Files</a>
        </div>
        
        <!-- Display files based on the selected option -->
        <ul>
            <?php while ($file = $files->fetch_assoc()) { ?>
                <li><a href="<?php echo htmlspecialchars($file['file_path']); ?>">View File (Uploaded on <?php echo htmlspecialchars($file['upload_date']); ?>)</a></li>
            <?php } ?>
        </ul>
    </div>

    <div class="comment-form">
        <form action="add_comment.php" method="POST">
            <h3>Add Comment</h3>
            <textarea name="comment" required></textarea>
            <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
            <button type="submit">Add Comment</button>
        </form>
    </div>
</div>

</body>
</html>

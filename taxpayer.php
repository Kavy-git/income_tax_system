<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Session user_id not set!";
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch taxpayer details using the user_id (assuming user_id corresponds to TaxpayerID in the Taxpayer table)
$query = "SELECT * FROM `Taxpayer` WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$taxpayer = $stmt->get_result()->fetch_assoc();

if (!$taxpayer) {
    echo "No taxpayer data found for this user.";
    exit;
}

// Now that we have the taxpayer, get the TaxpayerID (which corresponds to UserID in Notification table)
$taxpayer_id = $taxpayer['TaxpayerID'];  // TaxpayerID is the actual ID we use for notifications

// Fetch assigned professional's name based on taxpayer's tax_professional_id
$professional_query = "SELECT name, email, phone FROM TaxProfessional WHERE user_id = ?";
$professional_stmt = $conn->prepare($professional_query);
$professional_stmt->bind_param("i", $taxpayer['tax_professional_id']);
$professional_stmt->execute();
$professional = $professional_stmt->get_result()->fetch_assoc();

// Fetch most recent notification for taxpayer using the TaxpayerID
$recent_notification_query = "SELECT * FROM Notification WHERE UserID = ? ORDER BY Timestamp DESC LIMIT 1";
$recent_notification_stmt = $conn->prepare($recent_notification_query);
$recent_notification_stmt->bind_param("i", $taxpayer_id);  // Use taxpayer_id here
$recent_notification_stmt->execute();
$recent_notification = $recent_notification_stmt->get_result()->fetch_assoc();

// Fetch all notifications for taxpayer (excluding the most recent one)
$notifications_query = "SELECT * FROM Notification WHERE UserID = ? AND NotificationID != ? ORDER BY Timestamp DESC";
$notifications_stmt = $conn->prepare($notifications_query);
$notifications_stmt->bind_param("ii", $taxpayer_id, $recent_notification['NotificationID']);
$notifications_stmt->execute();
$notifications = $notifications_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taxpayer Dashboard</title>
    <style>
        body {
            background-color: #1a1a1a;
            color: #f0f0f0;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        .profile-icon {
            background-color: #444;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .profile-icon:hover {
            background-color: #666;
        }

        .profile-card {
            display: none;
            position: absolute;
            top: 50px;
            right: 0;
            background-color: #333;
            padding: 15px;
            border-radius: 5px;
            width: 200px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
        }

        .profile-card p {
            margin: 10px 0;
        }

        .profile-card h4 {
            margin: 0;
            font-size: 18px;
            color: #fff;
        }

        .profile-card span {
            color: #bbb;
        }

        .content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .notification-box {
            background-color: #444;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .notification-box h3 {
            margin-top: 0;
        }

        .notification-box ul {
            list-style: none;
            padding: 0;
        }

        .notification-box ul li {
            margin-bottom: 10px;
        }

        .form-container {
            background-color: #333;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .form-container input,
        .form-container button {
            margin-bottom: 15px;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 5px;
            background-color: #555;
            color: #fff;
        }

        .form-container button {
            background-color: #35F374;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #28a745;
        }
    </style>
</head>
<body>

<header>
    <h1>Taxpayer Dashboard</h1>
    <div class="profile-icon" onclick="toggleProfileCard()">
        👤
    </div>
    <div class="profile-card" id="profile-card">
        <h4><?php echo htmlspecialchars($taxpayer['Name']); ?></h4>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($taxpayer['Email'] ?? 'N/A'); ?></p>
        <p><strong>Phone No:</strong> <?php echo htmlspecialchars($taxpayer['Phone'] ?? 'N/A'); ?></p>
        <p><strong>Professional:</strong> <?php echo htmlspecialchars($professional['name'] ?? 'N/A'); ?></p>
        
    </div>
</header>

<div class="content">
    <h2>Hi, <?php echo htmlspecialchars($taxpayer['Name']); ?>!</h2>

    <div class="form-container">
        <h3>Upload Document</h3>
        <form action="upload_file.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="document" required>
            <button type="submit">Upload Document</button>
        </form>

        <h3>Add Revenue</h3>
        <form action="add_revenue.php" method="POST">
            <input type="number" name="revenue" placeholder="Enter Revenue" required>
            <button type="submit">Add Revenue</button>
        </form>
    </div>

    <div class="notification-box">
        <h3>Recent Notification</h3>
        <?php if ($recent_notification && isset($recent_notification['Message'])): ?>
            <p><?php echo htmlspecialchars($recent_notification['Message']); ?>
            <?php if (isset($recent_notification['Type']) && $recent_notification['Type'] === 'comment') { ?>
                <span>(Comment from Professional)</span>
            <?php } ?>
            </p>
        <?php else: ?>
            <p>No recent notifications.</p>
        <?php endif; ?>

        <h3>All Notifications</h3>
        <ul>
            <?php while ($notification = $notifications->fetch_assoc()) { ?>
                <li>
                    <?php if (isset($notification['Message'])) {
                        echo htmlspecialchars($notification['Message']);
                    } ?>
                    <?php if (isset($notification['Type']) && $notification['Type'] === 'comment') { ?>
                        <span>(Comment from Professional)</span>
                    <?php } ?>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>

<script>
    function toggleProfileCard() {
        var card = document.getElementById('profile-card');
        card.style.display = (card.style.display === 'block') ? 'none' : 'block';
    }
</script>

</body>
</html>

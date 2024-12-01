<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'taxprofessional') {
    die("Access denied.");
}

$professional_id = $_SESSION['user_id']; // Assuming the professional logs in

// Fetch tax professional's info
$professional_query = "SELECT * FROM TaxProfessional WHERE user_id = ?";
$professional_stmt = $conn->prepare($professional_query);
$professional_stmt->bind_param("i", $professional_id);
$professional_stmt->execute();
$professional_result = $professional_stmt->get_result();

if ($professional_result->num_rows > 0) {
    $professional = $professional_result->fetch_assoc();
} else {
    // Handle case where no professional data is found
    $professional = null;
}

// Fetch clients assigned to the professional
$clients_query = "SELECT * FROM Taxpayer WHERE tax_professional_id = ?";
$clients_stmt = $conn->prepare($clients_query);
$clients_stmt->bind_param("i", $professional_id);
$clients_stmt->execute();
$clients = $clients_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Professional Dashboard</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #121212; /* Dark mode background */
            color: #fff;  /* White text for dark mode */
            margin: 0;
            padding: 0;
        }

        /* Header Styling */
        header {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: flex-start; /* Align content to the left */
            align-items: center;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
        }

        .profile-icon {
            background-color: #444;
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-icon:hover {
            background-color: #666;
        }

        .profile-icon img {
            border-radius: 50%;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Dashboard Content */
        .dashboard-content {
            margin-top: 80px;  /* Adjust for fixed header */
            padding: 20px;
            text-align: center;
        }

        .client-card {
            background-color: #333;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
        }

        .client-card a {
            color: #35F374;  /* Green color for hover effect */
            text-decoration: none;
            font-size: 18px;
        }

        .client-card a:hover {
            color: #fff;
        }

        /* Profile Card */
        .profile-card {
            display: none;
            position: absolute;
            top: 60px;
            left: 10px; /* Align profile card next to profile icon */
            background-color: #333;
            padding: 15px;
            border-radius: 5px;
            width: 200px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
        }

        .profile-card h4 {
            margin: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #fff;
        }

        .profile-card p {
            margin: 10px 0;
        }
    </style>
</head>
<body>

<header>
    <!-- Profile Icon on the Left -->
    <div class="profile-icon" onclick="toggleProfileCard()">
           👤
    </div>
    <div class="header-title" style="flex-grow: 1; text-align: center;">
        <h1>Tax Professional Dashboard</h1>
    </div>
</header>

<!-- Profile Card Dropdown -->
<div class="profile-card" id="profileCard">

        <h4><?php echo htmlspecialchars($professional['name']); ?></h4>
        <p><strong>Professional:</strong> <?php echo htmlspecialchars($professional['name'] ?? 'N/A'); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($professional['email'] ?? 'N/A'); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($professional['phone'] ?? 'N/A'); ?></p>
    </div>
</div>

<div class="dashboard-content">
    <h1>Your Clients</h1>
    <?php while ($client = $clients->fetch_assoc()) { ?>
        <div class="client-card">
            <a href="view_client.php?client_id=<?php echo $client['user_id']; ?>">
                <?php echo htmlspecialchars($client['Name']); ?>
            </a>
        </div>
    <?php } ?>
</div>

<script>
    function toggleProfileCard() {
        var profileCard = document.getElementById('profileCard');
        profileCard.style.display = (profileCard.style.display === 'block') ? 'none' : 'block';
    }
</script>

</body>
</html>

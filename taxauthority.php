<?php
session_start();
include 'db.php';

// Ensure that only logged-in authorities can view the page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'taxauthority') {
    // Redirect to login page if the user is not logged in as authority
    header("Location: login.php");
    exit();
}

// Fetch Taxpayers Assigned to Professionals
function fetchTaxpayers($conn) {
    $query = "SELECT p.user_id AS professional_id, p.name AS professional_name,
                     t.user_id AS taxpayer_id, t.name AS taxpayer_name
              FROM TaxProfessional p
              LEFT JOIN Taxpayer t ON p.user_id = t.tax_professional_id";
    return $conn->query($query);
}

$taxpayers = fetchTaxpayers($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Authority</title>
    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #121212; /* Dark Background */
            color: #e0e0e0; /* Light Text */
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: auto;
            background: #1e1e1e; /* Dark container */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.7);
        }

        h1 {
            color: #f5f5f5; /* Light text for header */
            text-align: center;
        }

        button {
            margin: 10px 0;
            padding: 10px 20px;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #444;
            padding: 10px;
            text-align: center;
        }

        table th {
            background-color: #333; /* Dark background for headers */
            color: #fff;
        }

        table td {
            background-color: #222; /* Dark background for rows */
        }

        table tr:nth-child(even) td {
            background-color: #2b2b2b; /* Slightly lighter dark for even rows */
        }

        table tr:hover td {
            background-color: #444; /* Highlight row on hover */
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Tax Authority</h1>

    <!-- Navigation buttons -->
    <button onclick="window.location.href='add_professional.php'">Add TaxProfessional</button>
    <button onclick="window.location.href='delete_taxprofessional.php'">Delete TaxProfessional</button>

    <h2>Taxpayers Assigned to Professionals</h2>
    <table>
        <thead>
        <tr>
            <th>TaxProfessional ID</th>
            <th>TaxProfessional Name</th>
            <th>Taxpayer ID</th>
            <th>Taxpayer Name</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $taxpayers->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['professional_id']); ?></td>
                <td><?php echo htmlspecialchars($row['professional_name']); ?></td>
                <td><?php echo htmlspecialchars($row['taxpayer_id']); ?></td>
                <td><?php echo htmlspecialchars($row['taxpayer_name']); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>

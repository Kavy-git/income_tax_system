<?php
include 'db.php';  // Ensure your db.php file is connecting to the database properly

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST["name"];
    $address = $_POST["address"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);  // Encrypt password
    $role = $_POST["role"];
    $tin = "TIN" . rand(1000, 9999);  // Generate random TIN

    // Insert into User table first
    $userQuery = "INSERT INTO User (name, password, role, email, phone) VALUES (?, ?, ?, ?, ?)";
    $userStmt = $conn->prepare($userQuery);
    
    if ($userStmt === false) {
        die('MySQL prepare error for User table: ' . $conn->error);  // Debugging line to check User query
    }

    $userStmt->bind_param("sssss", $name, $password, $role, $email, $phone);
    
    if ($userStmt->execute()) {
        // Retrieve the last inserted user_id
        $user_id = $conn->insert_id;

        // Now insert into the appropriate role table based on the user_id
        if ($role == "taxpayer") {
            // SQL query for taxpayer
            $query = "INSERT INTO Taxpayer (user_id, name, tin, address, email, phone, registrationdate, password) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
            $stmt = $conn->prepare($query);
            
            if ($stmt === false) {
                die('MySQL prepare error for Taxpayer: ' . $conn->error);  // Debugging line to check SQL query
            }

            $stmt->bind_param("issssss", $user_id, $name, $tin, $address, $email, $phone, $password);

        } else {
            // SQL query for taxprofessional
            $certification_id = "CERT" . rand(1000, 9999);  // Generate random certification ID
            $query = "INSERT INTO TaxProfessional (user_id, name, tin, certification_id, email, phone, registrationdate, password) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
            $stmt = $conn->prepare($query);

            if ($stmt === false) {
                die('MySQL prepare error for TaxProfessional: ' . $conn->error);  // Debugging line to check SQL query
            }

            $stmt->bind_param("issssss", $user_id, $name, $tin, $certification_id, $email, $phone, $password);
        }

        // Execute the main role-specific insertion
        if ($stmt->execute()) {
            echo "User and role-specific data inserted successfully!";
            header("Location: login.php");  // Redirect to login page
            exit();
        } else {
            echo "Error inserting into main role table: " . $stmt->error;  // Print error message if query fails
        }

    } else {
        echo "Error inserting into User table: " . $userStmt->error;  // Print error message if query fails
    }

    // Close the prepared statements
    $userStmt->close();
    if (isset($stmt)) $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <style>
        /* General Reset */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* Body and Background */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #0a0b0b; /* Dark background color */
            color: #fff; /* Light text color for contrast */
            margin: 0;
        }

        /* Container Styling */
        .container {
            width: 500px; /* Increased width for a larger container */
            padding: 3em; /* Increased padding for more space */
            background: #1a1a1a; /* Darker background for container */
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }

        /* Heading Styling */
        h2 {
            text-align: center;
            color: #ff4757; /* Accent color for heading */
        }

        /* Form Styling */
        form { display: flex; flex-direction: column; }

        /* Input, Select, and Button Styling */
        input, select, button {
            padding: 12px; /* Slightly larger padding for inputs */
            margin: 10px 0; /* Increased margin */
            border-radius: 4px;
            border: 1px solid #333;
            background-color: #333; /* Dark input background */
            color: #fff; /* Light text color */
        }

        /* Button Styling */
        button {
            background-color: #28a745; /* Green for button */
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #218838;
        }

        /* Link Styling */
        .redirect-link {
            text-align: center;
            margin-top: 15px;
        }

        .redirect-link a {
            color: #ff4757; /* Accent color for links */
            text-decoration: none;
        }

        .redirect-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>
        <form method="POST" action="signup.php">
            <input type="text" name="name" placeholder="Name" required>
            <input type="text" name="address" placeholder="Address">
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Phone" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="hidden" name="tax_professional_id" value="125">
            <select name="role" required>
                <option value="taxpayer">Taxpayer</option>
                <option value="taxprofessional">TaxProfessional</option>
            </select>
            <button type="submit">Sign Up</button>
        </form>
        <div class="redirect-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>

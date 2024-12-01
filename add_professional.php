<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'db.php';

    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $tin = $_POST['tin'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $certification_id = $_POST['certification_id'];

    // Call the stored procedure to add a tax professional
    $query = "CALL AddTaxProfessional(?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssi", $name, $email, $phone, $tin, $password, $certification_id);

    if ($stmt->execute()) {
        echo "Tax Professional added successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add TaxProfessional</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #121212; /* Dark background */
            color: #E0E0E0; /* Light text */
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: #333333; /* Dark background for the form */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            color: #ffffff; /* White text for header */
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #555555; /* Darker border */
            border-radius: 4px;
            font-size: 16px;
            background-color: #444444; /* Dark background for inputs */
            color: #ffffff; /* White text in input fields */
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            border-color: #007BFF; /* Blue focus border */
            outline: none;
        }

        button {
            background-color: #007BFF; /* Blue button */
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        .message {
            text-align: center;
            margin-top: 15px;
            font-size: 18px;
            color: #32CD32; /* Green color for success message */
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 24px;
            }

            input[type="text"], input[type="email"], input[type="password"] {
                font-size: 14px;
            }

            button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Tax Professional</h1>
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Name" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="text" name="phone" placeholder="Phone" required><br>
            <input type="text" name="tin" placeholder="TIN" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <input type="text" name="certification_id" placeholder="Certification ID" required><br>
            <button type="submit">Add Professional</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($stmt) && $stmt->execute()) {
            echo "<div class='message'>Tax Professional added successfully.</div>";
        }
        ?>
    </div>
</body>
</html>

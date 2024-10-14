<?php
session_start();

// Clear any existing session data
session_unset();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $password = $_POST['password'];

    // Query to check user credentials
    $sql = "SELECT * FROM users WHERE username='$user'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Debugging: output the retrieved row
        echo '<pre>';
        print_r($row);
        echo '</pre>';

        // Verify password
        if (password_verify($password, $row['password'])) {
            // Store user info and role in the session
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // Ensure the role is set here

            // Debugging: confirm session variables are set
            echo "Session username: " . $_SESSION['username'] . "<br>";
            echo "Session role: " . $_SESSION['role'] . "<br>";

            // Redirect to the home page
            header("Location: home.php");
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No user found!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="user-icon">
            <img src="user-icon.png" alt="User Icon">
        </div>
        
        <h2>Dashboard Login</h2>
        
        <form method="post" action="login.php"> <!-- Ensure action is set to login.php -->
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="USERNAME" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="PASSWORD" required>
            </div>
            <button type="submit">LOGIN</button>
            <a href="#" class="forgot-password">Forgot password?</a>
        </form>
    </div>
</body>
</html>

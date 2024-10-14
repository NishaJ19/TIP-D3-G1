<?php
// Connect to the database
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "project"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 1: Delete all existing users from the 'users' table
$delete_sql = "DELETE FROM users";
if ($conn->query($delete_sql) === TRUE) {
    echo "All existing users deleted successfully.<br>";
} else {
    echo "Error deleting users: " . $conn->error . "<br>";
}

// Step 2: Define new users and insert them into the 'users' table

// New User 1: Admin user
$user1 = 'admin';
$plain_password1 = 'adminpassword';
$role1 = 'admin';

// Hash the password using PHP's password_hash() function
$hashed_password1 = password_hash($plain_password1, PASSWORD_DEFAULT);

// Insert admin user into the users table
$sql1 = "INSERT INTO users (username, password, role) VALUES ('$user1', '$hashed_password1', '$role1')";

// Execute the query for the first user
if ($conn->query($sql1) === TRUE) {
    echo "Admin user created successfully.<br>";
} else {
    echo "Error creating admin user: " . $sql1 . "<br>" . $conn->error . "<br>";
}

// New User 2: General user
$user2 = 'general_user';
$plain_password2 = 'userpassword';
$role2 = 'general';

// Hash the password for the general user
$hashed_password2 = password_hash($plain_password2, PASSWORD_DEFAULT);

// Insert general user into the users table
$sql2 = "INSERT INTO users (username, password, role) VALUES ('$user2', '$hashed_password2', '$role2')";

// Execute the query for the second user
if ($conn->query($sql2) === TRUE) {
    echo "General user created successfully.<br>";
} else {
    echo "Error creating general user: " . $sql2 . "<br>" . $conn->error . "<br>";
}

// Close the connection
$conn->close();
?>

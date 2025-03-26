<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testdb";
$log_file = "error_log.txt";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error . "\n", 3, $log_file);
    die("Connection failed. Please try again later.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    
    try {
        // Secure Query using Prepared Statements
        // Prevents from SQL injection
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");

        // Added error handing without compromising database
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $user);
        if (!$stmt->execute()) {
            throw new Exception("Statement execution failed: " . $stmt->error);
        }
        
        // Adds password hashing
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Verify the hashed password
            if (password_verify($pass, $row['password'])) {
                $_SESSION['user'] = $user;
                echo "Login successful!";
            } else {
                echo "Invalid credentials!";
            }
        } else {
            echo "Invalid credentials!";
        }
    } catch (Exception $e) {
        // Error Logging
        error_log($e->getMessage() . "\n", 3, $log_file);
        echo "An error occurred. Please try again later.";
    }
    
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Secure Login</title>
</head>
<body>
    <form method="POST" action="">
        <!-- Requires username and password -->
        <label>Username:</label>
        <input type="text" name="username" required><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
